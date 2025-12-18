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
?>
<style>
h1 {margin-bottom:0; text-align:center;}
h5 {font-size:1em; margin:40px 0 10px; font-weight:bold;}
p {margin:0 0 20px; text-align:center;}

.note {text-align:center;}
.note span {text-align:center; margin:0 20px 20px; padding:10px; color:#aaa; border:1px solid #ccc; background:#eee; display:inline-block; border-radius:3px;}
.note li {margin-bottom:5px;}

.code {display:block; margin:10px 0; padding:0; background:#eee; border:1px solid #ccc; border-radius:3px; position:relative;}
.code:before {content:"php"; position:absolute; top:5px; left:5px; display:block; font-size:80%; opacity:.5;}
.code textarea {width:100%; height:300px; padding:30px 10px 10px; display:inline-block; background:transparent; border:0; resize:vertical; font-family:monospace;}
.code.short textarea {height:200px;}
</style>
<h1>PHP Open AI Lib</h1>
<p>Interact with Artifical Intelligence</p>
<div class="note">
		<span>
			This library provides a simple and unified interface to interact with OpenAI models, enabling developers to integrate AI-powered features directly into their PHP applications.<br/>
			<br/>
			This library abstracts the complexity of OpenAI's APIs and exposes high-level methods to build conversational bots, generate and explain content, analyze and transform code, create images, and convert visual inputs into structured outputs such as HTML. It is designed to be easy to use, flexible, and suitable for both simple scripts and advanced applications.<br/>
			<br/>
			The library allows you to:<br/>
			<ul style="display:inline-block; text-align:left;">
				<li>Interact with OpenAI chat models using system and user messages.</li>
				<li>Build conversational AI bots with optional session handling.</li>
				<li>Generate raw or formatted AI responses.</li>
				<li>Explain documents, text, or source code using domain-specific personas.</li>
				<li>Automatically comment and document PHP code.</li>
				<li>Generate PHP code based on natural language instructions.</li>
				<li>Generate HTML snippets, full HTML pages, and UI layouts.</li>
				<li>Create images from text descriptions with configurable size and quality.</li>
				<li>Describe uploaded images and extract semantic information.</li>
				<li>Convert images into HTML structures using AI-based visual understanding.</li>
				<li>Handle errors and responses in a consistent and developer-friendly way.</li>
			</ul>
		</span>
</div>

<h2>Usage</h2>

<div>
	<h5>Interact with AI</h5>
	<div class="code">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIHandler.php";

$openai_encryption_key = "your openai key";

//init OpenAIHandler: new OpenAIHandler($openai_api_key, $url = null, $model = null, $max_tokens = null, $temperature = null)
$OpenAIHandler = new OpenAIHandler($openai_encryption_key, null, "gpt-4o"); 

//interact with AI bot and get its reply
$system_message = ""; //(optional) Define a system/context message that allows the bot to correctly interpret and assume the intended persona.
$user_message = ""; //your message or question
$reply = $OpenAIHandler->generateRawMessage($system_message, $user_message);
//or: $reply = $OpenAIHandler->generateMessage($system_content, $user_content);

//get errors - if any...
$errors = $OpenAIHandler->getErrors();

//show reply
echo $reply;

/* 
 * OpenAIHandler Main Methods:
 * - $items = $OpenAIHandler->generateImage($user_content, $number = null, $size = null, $quality = null, $options = array()); //generate images
 * - $reply = $OpenAIHandler->generateMessage($system_content, $user_content, $previous_messages = null, $options = array()); //get reply
 * - $reply = $OpenAIHandler->generateRawMessage($system_content, $user_content, $previous_messages = null, $options = array()); //get reply
 */
		</textarea>
	</div>
</div>

<div>
	<h5>Chat Bot</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$system_message = ""; //(optional) Define a system/context message that allows the bot to correctly interpret and assume the intended persona.
$user_message = ""; //your message or question

$res = OpenAIActionHandler::chat($openai_encryption_key, $system_message, $user_message, $session_id);
$bot_reply = $res["reply"] || null;
		</textarea>
	</div>
</div>

<div>
	<h5>Explain Content</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$type = "investments and pitches presentations"; //Define a system expertise that allows the bot to correctly assume the intended persona.
$content = file_get_contents("some_pitch_investor.ppt"); //content to be explained. This could be a code or a pdf or word or other doc text, etc...

$reply = OpenAIActionHandler::explainContent($openai_encryption_key, $type, $content);
		</textarea>
	</div>
</div>

<div>
	<h5>Comment Code</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$code = '$foo = 12; function bar($b) { return $b * 2; } bar($foo);'; //This is the php code to be explained.

$code_with_comments = OpenAIActionHandler::commentPHPCode($openai_encryption_key, $code);
		</textarea>
	</div>
</div>

<div>
	<h5>Generate Code</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Create function that duplicates the input. Then call that function assigned to foo variable."; //This are the instructions so the bot can generate the correspondent code

$res = OpenAIActionHandler::generatePHPCode($openai_encryption_key, $instructions);
$code = $res["code"] || null;
		</textarea>
	</div>
</div>

<div>
	<h5>Generate Html</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Create html with a list of cards with images.."; //This are the instructions so the bot can generate the correspondent html

$res = OpenAIActionHandler::generateHTMLCode($openai_encryption_key, $instructions);
$html = $res["html"] || null;
		</textarea>
	</div>
</div>

<div>
	<h5>Generate Web-Page</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Create personal portfolio page for a designer"; //This are the instructions so the bot can generate the correspondent page

$res = OpenAIActionHandler::generateHTMLPage($openai_encryption_key, $instructions);
$html = $res["html"] || null;
		</textarea>
	</div>
</div>

<div>
	<h5>Generate Images</h5>
	<div class="code short">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Image should have a blue sky and a little girl running on a forest"; //This are the instructions so the bot can generate the correspondent image
$num_of_images_to_create = 1; //(optional) number of images you wish the bot to create
$images_size = "1024x1024"; //(optional) generated image maximum pixles
$images_quality = "standard"; //(optional) generaed images quality

$res = OpenAIActionHandler::generateHTMLImage($openai_encryption_key, $instructions, $num_of_images_to_create, $images_size, $images_quality);
$items = $res["items"] || null; //list of remote urls for the generated images
		</textarea>
	</div>
</div>

<div>
	<h5>Describe Images</h5>
	<div class="code">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$files = array(
	array(
		"type" => "image/gif", //image content type
		"tmp_name" => "/tmp/sa765asd6", //local file path
		"name" => "bloxtor.gif" //file name
	),
	//...
);
$instructions = ""; //(optional) some extra instructions if apply

$reply = OpenAIActionHandler::describeImage($openai_encryption_key, $files, $instructions);
		</textarea>
	</div>
</div>

<div>
	<h5>Convert Images to Html</h5>
	<div class="code">
		<textarea readonly>
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$files = array(
	array(
		"type" => "image/gif", //image content type
		"tmp_name" => "/tmp/sa765asd6", //local file path
		"name" => "bloxtor.gif" //file name
	),
	//...
);
$instructions = ""; //(optional) some extra instructions if apply

$res = OpenAIActionHandler::convertImageToHTML($openai_encryption_key, $files, $instructions = null);
$html = $res["html"] || null;
		</textarea>
	</div>
</div>
