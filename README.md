# PHP Open AI Lib

> Original Repos:   
> - PHP Open AI Lib: https://github.com/a19836/phpopenailib/   
> - Bloxtor: https://github.com/a19836/bloxtor/

## Overview

**PHP Open AI Lib** is a library that provides a simple and unified interface to interact with OpenAI models, enabling developers to integrate AI-powered features directly into their PHP applications.

This library abstracts the complexity of OpenAI's APIs and exposes high-level methods to build conversational bots, generate and explain content, analyze and transform code, create images, and convert visual inputs into structured outputs such as HTML. It is designed to be easy to use, flexible, and suitable for both simple scripts and advanced applications.

The library allows you to:   
- Interact with OpenAI chat models using system and user messages.
- Build conversational AI bots with optional session handling.
- Generate raw or formatted AI responses.
- Explain documents, text, or source code using domain-specific personas.
- Automatically comment and document PHP code.
- Generate PHP code based on natural language instructions.
- Generate HTML snippets, full HTML pages, and UI layouts.
- Create images from text descriptions with configurable size and quality.
- Describe uploaded images and extract semantic information.
- Convert images into HTML structures using AI-based visual understanding.
- Handle errors and responses in a consistent and developer-friendly way.

To see a working example, open [index.php](index.php) on your server.

---

## Use Cases

- AI-powered chatbots and assistants
- Automated code generation and documentation
- Explaining and summarizing documents or source code
- Generating UI layouts, HTML pages, and assets
- Image generation, analysis, and transformation
- Rapid prototyping with AI-driven features

This library allows PHP applications to leverage modern AI capabilities with minimal effort, providing a clean, extensible, and developer-friendly interface to OpenAI.

---

## Usage

### Interact with AI

```php
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
```

### Chat Bot

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$system_message = ""; //(optional) Define a system/context message that allows the bot to correctly interpret and assume the intended persona.
$user_message = ""; //your message or question

$res = OpenAIActionHandler::chat($openai_encryption_key, $system_message, $user_message, $session_id);
$bot_reply = $res["reply"] || null;
```

### Explain Content

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$type = "investments and pitches presentations"; //Define a system expertise that allows the bot to correctly assume the intended persona.
$content = file_get_contents("some_pitch_investor.ppt"); //content to be explained. This could be a code or a pdf or word or other doc text, etc...

$reply = OpenAIActionHandler::explainContent($openai_encryption_key, $type, $content);
```

### Comment Code

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$code = '$foo = 12; function bar($b) { return $b * 2; } bar($foo);'; //This is the php code to be explained.

$code_with_comments = OpenAIActionHandler::commentPHPCode($openai_encryption_key, $code);
```

### Generate Code

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Create function that duplicates the input. Then call that function assigned to foo variable."; //This are the instructions so the bot can generate the correspondent code

$res = OpenAIActionHandler::generatePHPCode($openai_encryption_key, $instructions);
$code = $res["code"] || null;
```

### Generate Html

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Create html with a list of cards with images.."; //This are the instructions so the bot can generate the correspondent html

$res = OpenAIActionHandler::generateHTMLCode($openai_encryption_key, $instructions);
$html = $res["html"] || null;
```

### Generate Web-Page

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Create personal portfolio page for a designer"; //This are the instructions so the bot can generate the correspondent page

$res = OpenAIActionHandler::generateHTMLPage($openai_encryption_key, $instructions);
$html = $res["html"] || null;
```

### Generate Images

```php
include __DIR__ . "/lib/OpenAIActionHandler.php";

$openai_encryption_key = "your openai key";
$instructions = "Image should have a blue sky and a little girl running on a forest"; //This are the instructions so the bot can generate the correspondent image
$num_of_images_to_create = 1; //(optional) number of images you wish the bot to create
$images_size = "1024x1024"; //(optional) generated image maximum pixles
$images_quality = "standard"; //(optional) generaed images quality

$res = OpenAIActionHandler::generateHTMLImage($openai_encryption_key, $instructions, $num_of_images_to_create, $images_size, $images_quality);
$items = $res["items"] || null; //list of remote urls for the generated images
```

### Describe Images

```php
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
```

### Convert Images to Html

```php
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
```

