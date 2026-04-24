<?php
// Wrapper file that loads the upstream PHPMailer source in the repo.
// This keeps `email_setup/PHPMailer` usable while avoiding huge duplicated files.
require_once __DIR__ . '/../../PHPMailer-master/src/PHPMailer.php';
