<?php
$env = require __DIR__ . '/env.php';

define('GROQ_API_KEY', $env['GROQ_API_KEY']);
define('GROQ_MODEL', $env['GROQ_MODEL'] ?? 'llama-3.3-70b-versatile');