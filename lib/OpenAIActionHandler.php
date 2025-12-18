<?php
/*
 * Copyright (c) 2025 Bloxtor (http://bloxtor.com) and Joao Pinto (http://jplpinto.com)
 * 
 * Multi-licensed: BSD 3-Clause | Apache 2.0 | GNU LGPL v3 | HLNC License (http://bloxtor.com/LICENSE_HLNC.md)
 * Choose one license that best fits your needs.
 *
 * Original PHP Open AI Lib Repo: https://github.com/a19836/phpopenailib/
 * Original Bloxtor Repo: https://github.com/a19836/bloxtor
 *
 * YOU ARE NOT AUTHORIZED TO MODIFY OR REMOVE ANY PART OF THIS NOTICE!
 */

include_once __DIR__ . "/OpenAIHandler.php";
include_once __DIR__ . "/PHPCodePrintingHandler.php";

class OpenAIActionHandler {
	
	public static function chat($openai_encryption_key, $system_message, $user_message, $session = null) {
		$session_histories = null;
		
		if ($session) {
			// Get all sessions histories
			$sessions_histories = array(); //TODO: get sessions from php session
			
			// get current session history
			$session_histories = isset($sessions_histories[$session]) ? $sessions_histories[$session] : null;
			
			// Truncate some previous $session_histories (FIFO), so it doesn't exceed the openai maximum tokens
			//TODO
		}
		
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o");
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		$system_tokens = $OpenAIHandler->estimateTextTokens($system_message);
		$user_tokens = $OpenAIHandler->estimateTextTokens($user_message);
		$max_tokens = floor($model_tokens_limits - $system_tokens - $sql_tokens - 10); //margin of 10 tokens
		
		//if max_tokens is less than minimum tokens, then cut the system message accordingly. minimum 1000 tokens for a good response.
		$minimum_tokens = 11600;
		
		if ($max_tokens < $minimum_tokens) {
			$diff = $max_tokens > 0 ? $minimum_tokens - $max_tokens : $minimum_tokens + abs($max_tokens);
			$max_tokens = $minimum_tokens;
			
			if ($diff > $system_tokens)
				$system_message = null;
			else if ($diff > 0) {
				$chunks = $OpenAIHandler->splitTextByTokens($system_message, $system_tokens - $diff);
				$system_message = $chunks[0] . "\nThis message has been truncated...";
			}
		}
		
		$reply = $OpenAIHandler->generateRawMessage($system_message, $user_message, $session_histories);
		$errors = $OpenAIHandler->getErrors();
		
		if (empty($errors)) {
			if ($session) {
				// Initialize session history if it doesn't exist
				if (!isset($sessions_histories[$session]))
					$sessions_histories[$session] = array();
				
				// Add the user message to the session history
				$session_histories[$session][] = array("role" => "user", "content" => $user_message);
				
				// Add the OpenAI response to the session history
				$session_histories[$session][] = array("role" => "assistant", "content" => $reply);
				
				// Save session_histories for future user messages
				//TODO: save sessions to php session
			}
			
			return array(
				"reply" => $reply
			);
		}
		else {
			$error_msg = "Error explaning logs. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function explainContent($openai_encryption_key, $type, $input) {
		$system_content = "You are a expert that explains $type.";
		
		//get max_tokens, based in lines with code - a comment per line of code
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key);
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
		$sql_tokens = $OpenAIHandler->estimateTextTokens($input);
		$max_tokens = floor($model_tokens_limits - $system_tokens - $sql_tokens - 10); //margin of 10 tokens
		//echo "$model_tokens_limits|$system_tokens|$sql_tokens|$max_tokens";die();
		
		$reply = $OpenAIHandler->generateMessage($system_content, $input, null, array("max_tokens" => $max_tokens));
		$errors = $OpenAIHandler->getErrors();
		
		if (empty($errors))
			return $reply;
		else {
			$error_msg = "Error explaning sql. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function commentPHPCode($openai_encryption_key, $input) {
		$system_content = "1. Instructions: You are a PHP and HTML expert. Do not change, fix or indent the code. Only comment the code. Do not add any extra sentences, explanations, or code formatting such as backticks, '```', `php`, or other symbols. Your response should consist solely of PHP and HTML code and correspondent comments inside of php code, with no additional formatting or text. If code is the same, give different comments. The reply will be concatenated with previous replies and displayed directly in a code editor.
2. What to do: Comment the code in the user message.";
		
		//get max_tokens, based in lines with code - a comment per line of code
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key);
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
		$max_tokens = floor( ($model_tokens_limits - $system_tokens) / 4 * 3 ); //give 3 times more commenting words than the code.
		//echo "$model_tokens_limits|$system_tokens|$max_tokens";die();
		
		$code_without_comments = PHPCodePrintingHandler::getCodeWithoutComments($input);
		$reply = $OpenAIHandler->generateMessage($system_content, $code_without_comments, null, array("max_tokens" => $max_tokens));
		$errors = $OpenAIHandler->getErrors();
		
		if (empty($errors)) {
			//remove ``` format, added by openai
			$reply = preg_replace('/(^|\n)```(php)?\s*/', '', $reply);
			$reply = preg_replace('/\n```\s*(\n|$)/', '', $reply);
			//echo $reply;die();
			
			$input = self::mergeCommentsIntoCode($input, $reply);
			
			return $input;
		}
		else {
			$error_msg = "Error commenting php code. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function generatePHPCode($openai_encryption_key, $input) {
		$system_content = "1. Instructions: You are a PHP expert. Do not add any extra sentences, explanations, or code formatting such as backticks, '```', `php`, or other symbols. Your response should consist solely of PHP code, with no additional formatting or text. If request is the same, create a different PHP. The reply will be concatenated with previous replies and displayed directly in a PHP editor.
2. What to do: Create PHP code based on the user message.";
		
		//get max_tokens, based in lines with code - a comment per line of code
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o");
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
		$user_request_tokens = $OpenAIHandler->estimateTextTokens($input);
		$max_tokens = floor($model_tokens_limits - $system_tokens - $user_request_tokens - 10); //margin of 10 tokens
		//echo "$model_tokens_limits|$system_tokens|$user_request_tokens|$max_tokens";die();
		
		$reply = $OpenAIHandler->generateMessage($system_content, $input, null, array("max_tokens" => $max_tokens));
		$errors = $OpenAIHandler->getErrors();
		
		if (empty($errors)) {
			//remove ``` format, added by openai
			$reply = preg_replace('/(^|\n)```(php)?\s*/', '', $reply);
			$reply = preg_replace('/\n```\s*(\n|$)/', '', $reply);
			//echo $reply;die();
			
			return array(
				"code" => $reply
			);
		}
		else {
			$error_msg = "Error creating php. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function generateHTMLCode($openai_encryption_key, $input) {
		$system_content = "1. Instructions: You are a HTML expert. Do not add any extra sentences, explanations, or code formatting such as backticks, '```', `html`, or other symbols. Your response should consist solely of HTML code, with no additional formatting or text or other javascript or css files to download, since this code should be inside of HTML. If request is the same, create a different HTML. The reply will be concatenated with previous replies and displayed directly in a HTML editor. Add placeholder URLs to the images unless other instructions are provided in the user message.
2. What to do: Create HTML code based on the user message.";
		
		//get max_tokens, based in lines with code - a comment per line of code
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o");
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
		$user_request_tokens = $OpenAIHandler->estimateTextTokens($input);
		$max_tokens = floor($model_tokens_limits - $system_tokens - $user_request_tokens - 10); //margin of 10 tokens
		//echo "$model_tokens_limits|$system_tokens|$user_request_tokens|$max_tokens";die();
		
		$reply = $OpenAIHandler->generateMessage($system_content, $input, null, array("max_tokens" => $max_tokens));
		$errors = $OpenAIHandler->getErrors();
		
		if (empty($errors)) {
			//remove ``` format, added by openai
			$reply = preg_replace('/(^|\n)```(html)?\s*/', '', $reply);
			$reply = preg_replace('/\n```\s*(\n|$)/', '', $reply);
			//echo $reply;die();
			
			return array(
				"html" => $reply
			);
		}
		else {
			$error_msg = "Error creating html. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function generateHTMLImage($openai_encryption_key, $instructions, $images_total, $images_size, $images_quality) {
		if ($instructions) {
			$system_content = "1. Instructions: You are a designer expert in creating images. Do not add any extra sentences, explanations, or code formatting such as backticks, '```', `html`, or other symbols. Your response should consist solely of urls list, with no additional formatting or text.
2. What to do: Create images and send the correspondent urls in a json format.";
			
			$OpenAIHandler = new OpenAIHandler($openai_encryption_key);
			$items = $OpenAIHandler->generateImage($system_content . "\n" . $instructions, $images_total, $images_size, $images_quality, array("url" => "https://api.openai.com/v1/images/generations", "model" => "dall-e-3"));
			$errors = $OpenAIHandler->getErrors();
			
			if (empty($errors)) {
				//echo $reply;die();
				
				return array(
					"items" => $items
				);
			}
			else {
				$error_msg = "Error generating image(s). Errors:\n- " . implode("\n- ", $errors);
				debug_log($error_msg, "error");
			}
		}
		else {
			$error_msg = "No image description to be generated or wrong input format.";
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function generateHTMLPage($openai_encryption_key, $instructions) {
		if ($instructions) {
			$system_content = "1. Instructions: You are a WebDesigner and Designer UX expert in HTML, CSS and Javascript building beautiful pages. Do not add any extra sentences, explanations, or code formatting such as backticks, '```', `html`, or other symbols. Your response should consist solely of HTML code, with no additional formatting or text. The generated HTML should contain the doctype, html, head and body tags. It can also include the necessary `<link>` and `<script>` tags for external ui libraries, like bootstrap, jquery-ui or other libraries you wish to add. Add placeholder URLs to the images unless other instructions are provided in the user message.
2. What to do: Create HTML code based on the user message.";
			
			//get max_tokens, based in lines with code - a comment per line of code
			$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o");
			$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
			$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
			$user_request_tokens = $OpenAIHandler->estimateTextTokens($instructions);
			$max_tokens = floor($model_tokens_limits - $system_tokens - $user_request_tokens - 10); //margin of 10 tokens
			//echo "$model_tokens_limits|$system_tokens|$user_request_tokens|$max_tokens";die();
			
			$html = $OpenAIHandler->generateRawMessage($system_content, $instructions, null, array("max_tokens" => $max_tokens));
			$errors = $OpenAIHandler->getErrors();
			
			if (empty($errors)) {
				//remove ``` format, added by openai
				$html = preg_replace('/(^|\n)```(html)?\s*/', '', $html);
				$html = preg_replace('/\n```\s*(\n|$)/', '', $html);
				
				return array(
					"html" => $html
				);
			}
			else {
				$error_msg = "Error creating html. Errors:\n- " . implode("\n- ", $errors);
				debug_log($error_msg, "error");
			}
		}
		else {
			$error_msg = "No page description to be generated or wrong input format.";
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	//although this allows multiple images, its recomended to only pass 1 image, so it returns a longer description.
	public static function describeImage($openai_encryption_key, $files, $instructions = null) {
		$system_content = "You are an assistant describing images for blind people.";
		$user_content = array(
			array(
				"type" => "text", 
				"text" => "Analyze and describe the following image in detail including colors, spacing and all details. The file name is : `" . $file["name"] . "`." . ($instructions ? "\n" . $instructions : null)
			)
		);
		
		if ($files)
			foreach ($files as $file)
				$user_content[] = array(
					"type" => "image_url",
					"image_url" => array(
						"url" =>  "data:" . $file["type"] . ";base64," . utf8_decode(base64_encode(file_get_contents($file["tmp_name"]))), // either url (not local) or base64. file id is used only in assistants api.
					),
				);
		
		//get max_tokens, based in lines with code - a comment per line of code
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o");
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
		$user_tokens = $OpenAIHandler->estimateTextTokens($user_content);
		$max_tokens = floor($model_tokens_limits - $system_tokens - $user_tokens - 10); //margin of 10 tokens
		//echo "$model_tokens_limits|$system_tokens|$sql_tokens|$max_tokens";die();
		
		if ($max_tokens < 500)
			$max_tokens = 500;
		
		$reply = $OpenAIHandler->generateRawMessage($system_content, $user_content, null, array("model" => "gpt-4o", "max_tokens" => $max_tokens));
		
		if (empty($errors))
			return $reply;
		else {
			$error_msg = "Error describing image. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function convertImageToHTML($openai_encryption_key, $files, $instructions = null) {
		$system_content = "1. Instructions: You are a WebDesigner and Designer UX expert in converting images into HTML, CSS and Javascript building beautiful layouts. Do not add any extra sentences, explanations, or code formatting such as backticks, '```', `html`, or other symbols. Your response should consist solely of HTML code, with no additional formatting or text. The generated HTML should contain the doctype, html, head and body tags. It can also include the necessary `<link>` and `<script>` tags for external ui libraries, like bootstrap, jquery-ui or other libraries you wish to add. Add placeholder URLs to the images unless other instructions are provided in the user message.
2. What to do: Create HTML code based on the user images.";
		$user_content = array();
		
		if ($instructions)
			$user_content[] = array(
				"type" => "text", 
				"text" => $instructions
			);
		
		if ($files)
			foreach ($files as $file)
				$user_content[] = array(
					"type" => "image_url",
					"image_url" => array(
						"url" =>  "data:" . $file["type"] . ";base64," . utf8_decode(base64_encode(file_get_contents($file["tmp_name"]))), // either url (not local) or base64. file id is used only in assistants api.
					),
				);
		
		//get max_tokens, based in lines with code - a comment per line of code
		$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o");
		$model_tokens_limits = $OpenAIHandler->getDefaultModelTokensLimits();
		
		/*$system_tokens = $OpenAIHandler->estimateTextTokens($system_content);
		$user_tokens = $OpenAIHandler->estimateTextTokens($user_content);
		$max_tokens = floor($model_tokens_limits - $system_tokens - $user_tokens - 10); //margin of 10 tokens
		//echo "$model_tokens_limits|$system_tokens|$user_tokens|$max_tokens";die();
		
		if ($max_tokens < 500)
			$max_tokens = 500;
		*/
		$max_tokens = $model_tokens_limits; //for some reason, the model_tokens_limits works fine
		
		$html = $OpenAIHandler->generateRawMessage($system_content, $user_content, null, array("model" => "gpt-4o", "max_tokens" => $max_tokens));
		
		if (empty($errors)) {
			//remove ``` format, added by openai
			$html = preg_replace('/(^|\n)```(html)?\s*/', '', $html);
			$html = preg_replace('/\n```\s*(\n|$)/', '', $html);
				
			return array(
				"html" => $html
			);
		}
		else {
			$error_msg = "Error converting image to HTML. Errors:\n- " . implode("\n- ", $errors);
			debug_log($error_msg, "error");
		}
		
		return null;
	}
	
	public static function mergeCommentsIntoCode($input_code, $reply_code) {
		$input_lines = explode("\n", $input_code);  // Break the input code into lines
		$reply_lines = explode("\n", $reply_code);  // Break the reply code into lines
		$output_lines = [];  // Array to store the output code with comments
		$reply_lines_total = count($reply_lines);
		
		// Create an index of the lines in reply_code with their respective comments
		$reply_index_for_comments = [];
		$comment_buffer = '';
		
		foreach ($reply_lines as $reply_index_line => $reply_line) {
			// If it's a comment, buffer it
			if (preg_match('/^(\/\/|#|\/\*.*\*\/)/', trim($reply_line)))
				$comment_buffer .= "\n" . $reply_line;
			else if ($comment_buffer !== '') { // If it's code, store the comment buffer for that line
				$trimmed_reply_line_hash = trim($reply_line);
				
				if (!is_array($reply_index_for_comments[$trimmed_reply_line_hash]))
					$reply_index_for_comments[$trimmed_reply_line_hash] = array();
				
				$reply_index_for_comments[$trimmed_reply_line_hash][] = trim($comment_buffer);
				$comment_buffer = '';  // Reset the comment buffer
			}
		}

		// Now process input_code and find the corresponding comments from reply_code
		$repeated_lines_index = array();
		
		foreach ($input_lines as $input_index => $input_line) {
			$trimmed_input_line_hash = trim($input_line);
			
			if ($trimmed_input_line_hash) { //avoid searching of blank lines
				// Append the comment buffer to the output
				if (isset($reply_index_for_comments[$trimmed_input_line_hash])) {
					$i = isset($repeated_lines_index[$trimmed_input_line_hash]) ? $repeated_lines_index[$trimmed_input_line_hash] : 0;
					
					if (isset($reply_index_for_comments[$trimmed_input_line_hash][$i])) {
						//get indentation from input_line
						preg_match("/^(\s+)/", $input_line, $match, PREG_OFFSET_CAPTURE);
						$indentation = $match && isset($match[1][0]) ? $match[1][0] : "";
						
						//add comment
						$comment = $reply_index_for_comments[$trimmed_input_line_hash][$i];
						$comment = $indentation . str_replace("\n", "\n" . $indentation, $comment);
						$output_lines[] = $comment;
						
						//update index for next repeated line
						$repeated_lines_index[$trimmed_input_line_hash] = $i + 1;
					}
				}
			}
			
			// Add the current input line to the output
			$output_lines[] = $input_line;
		}

		// Convert the output_lines array back to a string
		return implode("\n", $output_lines);
	}
}
?>
