<?php
header('Content-Type: application/json');

if (empty($_POST)) {
	die(json_encode(["status" => false, "mensagem" => "Por favor, preencha todos os campos."]));
}

require '../vendor/autoload.php';

use Mailgun\Mailgun;

$dotenv = new Dotenv\Dotenv(__DIR__."/..");
$dotenv->load();

$post = [
    "Nome" => $_POST["name"],
    "E-mail" => $_POST["mail"],
    "Telefone" => $_POST["tel"],
    "Municipio" => $_POST["mun"],
    "Mensagem" => $_POST["text"],
    "Endereço IP" => $_SERVER['REMOTE_ADDR'],
    "Navegador" => $_SERVER['HTTP_USER_AGENT'],
];

$body = "Segue dados do contato: <br /><br />";

foreach($post as $label => $value) {
    $body .= "<b>$label</b>: <i>".($value ? $value : "N/D")."</i><br />";
}

# Instantiate the client.
$key = getenv("MAILGUN_KEY");
$domain = getenv("MAILGUN_DOMAIN");
$from = getenv("CONTACT_FROM");
$to = getenv("CONTACT_TO");

$mgClient = new Mailgun($key);

$emailAddress = filter_var($_POST['mail'], FILTER_SANITIZE_EMAIL);

try {
	$result = $mgClient->sendMessage("$domain", [
		'from'    => $from,
        'to'      => $to,
        'h:reply-to' => $emailAddress,
        'subject' => "[Busca Ativa Escolar] Contato via site - {$emailAddress}",
        'html'    => $body
	]);

} catch (\Exception $ex) {
    die(json_encode([
    	"status" => false,
	    "mensagem" => "Ops! Estamos sem sistema no momento.",
	    "exception" => $ex->getMessage(),
    ]));
}

die(json_encode([
	"status" => true,
	"mensagem" => "Seu contato foi para análise, entraremos em contato assim que possível."
]));