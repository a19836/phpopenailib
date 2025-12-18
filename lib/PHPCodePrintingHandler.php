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

class PHPCodePrintingHandler {
	
	public static function getCodeWithoutComments($code) {
		if ($code && (strpos($code, "//") !== false || strpos($code, "/*") !== false)) {
			$new_code  = '';
		
			$comment_tokens = array(T_COMMENT);
		
			if (defined('T_DOC_COMMENT'))
				$comment_tokens[] = T_DOC_COMMENT; // PHP 5
			if (defined('T_ML_COMMENT'))
				$comment_tokens[] = T_ML_COMMENT;  // PHP 4
		
			$tokens = token_get_all($code);

			foreach ($tokens as $token) {    
				if (is_array($token)) {
					if (in_array($token[0], $comment_tokens))
					    continue 1;

					$token = $token[1];
				}

				$new_code .= $token;
			}

			return $new_code;
		}
		
		return $code;
	}
}
?>
