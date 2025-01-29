<?php

require __DIR__ . '/../api/vendor/autoload.php';
require __DIR__ . '/customer.php';

use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Message;
use skrtdev\Telegram\CallbackQuery;

define("BOT_TOKEN", '7704395019:AAHd92VQWIJsIFEv99S-IRmjdwud9YwsjeM');
define("DEVELOPER_ID",6543275207 );
define("CHANNEL_ID",-1002378744173 );
define("LOGGING_ID",-1002391788871 );

$Bot = new Bot(BOT_TOKEN, [
    'parse_mode' => 'HTML',
    'debug' => LOGGING_ID,
    'skip_old_updates' => true
]);

$Bot->addErrorHandler(function (Throwable $e) use ($Bot) {
    $Bot->debug((string)$e);
});

// Função para verificar se o cliente é válido
function isValidCustomer($message, $customer_id) {
    // Verificar se o customer_id é válido e não é um bot
    if (!$customer_id || isBot($customer_id)) {
        $message->reply("You entered invalid data or a bot can't use this service!");
        return false;
    }
    
    // Verificar se o cliente existe
    try {
        $customer = new Customer($customer_id);
    } catch (Exception $e) {
        $message->reply("Customer not found.");
        return false;
    }
    
    return $customer;
}

// Função de log para ações administrativas
function logAdminAction($user, $action, $customer_id, $client) {
    $msg  = "‼️ <b>{$action}</b> ‼️\n\n";
    $msg .= "Admin: <i>" . (isset($user->user->username) ? "@{$user->user->username}" : htmlspecialchars("[{$user->user->first_name}]")) . "</i>\n";
    $msg .= "Customer: <i>" . (isset($client->user->username) ? "@{$client->user->username}" : htmlspecialchars("[{$customer_id}]")) . "</i>";
    $Bot->sendMessage(LOGGING_ID, $msg, ['parse_mode' => 'HTML']);
}

// Função para verificar se o usuário tem permissão
function hasPermission(Message $message) {
    return $message->from->id == DEVELOPER_ID;
}

// Função para verificar se o cliente é um bot
function isBot($customer_id) {
    global $Bot;
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    return $client->user->is_bot;
}

// Função para enviar a resposta com os dados do cliente
function sendCustomerInfo(Message $message, $customer) {
    $msg  = "Customer Information:\n\n";
    $msg .= "<b>Telegram ID:</b> <code>" . htmlspecialchars($customer->getTelegramID()) . "</code>\n";
    $msg .= "<b>Access Key:</b> <code>" . htmlspecialchars($customer->getAccessKey()) . "</code>\n";
    $msg .= "<b>Balance:</b> <code>" . htmlspecialchars($customer->getIsUnlimited() ? 'Unlimited' : number_format($customer->getCredits(), 0, '', '')) . "</code>\n\n";
    $msg .= "<b>Blocked:</b> <code>" . htmlspecialchars($customer->getIsBlocked() ? "Yes" : "No") . "</code>";
    
    $message->reply($msg);
}

// Função para escapar caracteres HTML
function escapeHTML($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

$Bot->onCommand('start', function (Message $message) use ($Bot) {
    if ($message->chat->type != 'private') return;

    try {
        $customer = new Customer($message->from->id);
    } catch (Exception $e) {
        $message->reply("Error: " . htmlspecialchars($e->getMessage()));
        return;
    }

    try {
        $chat = $Bot->getChat(CHANNEL_ID);
        $response = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
        if (!in_array($response->status, ['member', 'administrator', 'creator'])) {
            $message->reply("Join our <b>Channel</b> in order to continue.\nThen send /start", [
                'reply_markup' => json_encode([
                    'resize_keyboard' => true,
                    'inline_keyboard' => [
                        [
                            ['text' => "{$chat->title}", 'url' => "{$chat->invite_link}"]
                        ]
                    ]
                ])
            ]);
            return;
        }
    } catch (Exception $e) {
        $message->reply("Could not check channel membership. Please try again later.");
        return;
    }

    $msg = "BEM VINDO AO  <b>HELP_BOT_RH</b>!\nFIQUE AVONTADE PARA EXPLORAR NOSSOS RECURSOS\n\n";
    $msg .= "SUAS INFORMACOES:\n\n";
    $msg .= "<b>TELEGRAM ID:</b> <code>" . escapeHTML($customer->getTelegramID()) . "</code>\n";
    $msg .= "<b>CHAVE DE ACESSO:</b> <code>" . escapeHTML($customer->getAccessKey()) . "</code>\n";
    $msg .= "<b>SALDO:</b> <code>" . ($customer->getIsUnlimited() ? 'ILIMITADO' : number_format($customer->getCredits(), 0, '', '')) . "</code>\n\n";
    $msg .= "FIQUE ATENTO AO CANAL CONEXÃO RH, NOVIDADES SERAM POSTADAS POR LA\n";
    $msg .= "SUPORTE @RHUANCPM\n\n";
    $msg .= "VOLTE SEMPRE!";

    $message->reply($msg, [
        'reply_markup' => json_encode([
            'resize_keyboard' => true,
            'inline_keyboard' => [
                [
                    ['text' => 'REVOGAR CHAVE DE ACESSO', 'callback_data' => 'revoke_access_key']
                ]
            ]
        ])
    ]);
});

$Bot->onCallbackData('back_home', function (CallbackQuery $callback_query) {
    try {
        $customer = new Customer($callback_query->from->id);
    } catch (Exception $e) {
        $callback_query->message->editText("Error: " . htmlspecialchars($e->getMessage()));
        return;
    }

    $msg = "BEM VINDO AO  <b>HELP_BOT_RH</b>!\nFIQUE AVONTADE PARA EXPLORAR NOSSOS RECURSOS\n\n";
    $msg .= "SUAS INFORMACOES:\n\n";
    $msg .= "<b>TELEGRAM ID:</b> <code>" . escapeHTML($customer->getTelegramID()) . "</code>\n";
    $msg .= "<b>CHAVE DE ACESSO:</b> <code>" . escapeHTML($customer->getAccessKey()) . "</code>\n";
    $msg .= "<b>SALDO:</b> <code>" . ($customer->getIsUnlimited() ? 'ILIMITADO' : number_format($customer->getCredits(), 0, '', '')) . "</code>\n\n";
    $msg .= "FIQUE ATENTO AO CANAL CONEXÃO RH, NOVIDADES SERAM POSTADAS POR LA\n";
    $msg .= "SUPORTE @RHUANCPM\n\n";
    $msg .= "VOLTE SEMPRE!";

    $callback_query->message->editText($msg, [
        'reply_markup' => json_encode([
            'resize_keyboard' => true,
            'inline_keyboard' => [
                [
                    ['text' => 'Revoke Access Key', 'callback_data' => 'revoke_access_key']
                ]
            ]
        ])
    ]);
});

$Bot->onCallbackData('revoke_access_key', function (CallbackQuery $callback_query) {
    $customer = new Customer($callback_query->from->id);
    $nak = $customer->revokeAccessKey();
    $msg = "SUA NOVA CHAVE DE ACESSO E: <code>" . htmlspecialchars($nak) . "</code>";
    $callback_query->answer('SUA CHAVE DE ACESSO FOI ALTERADA COM SUCESSO!');
    $callback_query->message->editText($msg, [
        'reply_markup' => json_encode([
            'resize_keyboard' => true,
            'inline_keyboard' => [
                [
                    ['text' => 'back 🔙', 'callback_data' => "back_home"]
                ]
            ]
        ])
    ]);
});

$Bot->onCommand('check', function (Message $message, array $args = []) use ($Bot) {
    // Verificar permissão
    if (!hasPermission($message)) {
        $message->reply("You don't have permission to use this command.");
        return;
    }

    // Definir o customer_id com base no tipo de chat
    if (in_array($message->chat->type, ['supergroup', 'group'])) {
        if (!isset($message->reply_to_message)) {
            $message->reply("You need to reply to a user's message to use this command!");
            return;
        }
        $customer_id = $message->reply_to_message->from->id ?? null;
    } else {
        $customer_id = $args[0] ?? null;
    }

    // Verificar se o customer_id foi fornecido
    if (!$customer_id) {
        $message->reply("You must provide a valid user ID.");
        return;
    }

    // Verificar se é um bot
    if (isBot($customer_id)) {
        $message->reply("Bot can't use this service!");
        return;
    }

    // Obter o cliente
    try {
        $customer = new Customer($customer_id);
        sendCustomerInfo($message, $customer);
    } catch (Exception $e) {
        // Caso o cliente não exista ou algum erro ocorra
        $message->reply("Could not find customer information.");
    }
});

$Bot->onCommand('give', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);

    // Verificar permissão
    if (!hasPermission($message)) {
        $message->reply("somente o administrador tem permissão para usar esse comando.");
        return;
    }

    // Obter o customer_id e o saldo
    if (in_array($message->chat->type, ['supergroup', 'group'])) {
        if (!isset($message->reply_to_message)) {
            $message->reply("Você precisa responder à mensagem de um usuário para usar este comando!");
            return;
        }
        $customer_id = $message->reply_to_message->from->id;
        $balance = $args[0] ?? null;
    } else {
        $customer_id = $args[0] ?? null;
        $balance = $args[1] ?? null;
    }

    // Validar dados
    if (!$customer_id || !is_numeric($balance) || $balance <= 0) {
        $message->reply("Entrada inválida! Forneça um ID de usuário válido e um saldo positivo.");
        return;
    }

    // Verificar se é um bot
    if (isBot($customer_id)) {
        $message->reply("O bot não pode usar este serviço!");
        return;
    }

    // Instanciar o cliente
    try {
        $customer = new Customer($customer_id);
    } catch (Exception $e) {
        $message->reply("Erro: não foi possível criar ou recuperar o cliente.");
        return;
    }

    // Verificar e atualizar saldo
    if ($customer->getIsUnlimited()) {
        $message->reply("Este cliente tem a assinatura ilimitada!");
        return;
    }

    $oldBalance = $customer->getCredits();
    $customer->setCredits($balance, "[+]");
    $newBalance = $oldBalance + $balance;

    // Enviar mensagem de sucesso
    $msg  = "<b>SALDO ADICIONADO COM SUCESSO</b>\n\n";
    $msg .= "ANTIGO SALDO: <code>" . htmlspecialchars(number_format($oldBalance, 0, '', '')) . "</code>\n";
    $msg .= "NOVO SALDO: <code>" . htmlspecialchars(number_format($newBalance, 0, '', '')) . "</code>";
    $message->reply($msg, ['parse_mode' => 'HTML']);

    // Logar operação
    $logMsg  = "‼️ <b>Admin gave Customer Credits</b> ‼️\n\n";
    $logMsg .= "Given Credits: <i>" . htmlspecialchars($balance) . "</i>\n";
    $logMsg .= "Admin: <i>" . htmlspecialchars("[{$message->from->id}]") . "</i>\n";
    $logMsg .= "Customer: <i>" . htmlspecialchars("[{$customer_id}]") . "</i>";
    $Bot->sendMessage(LOGGING_ID, $logMsg, ['parse_mode' => 'HTML']);
});

$Bot->onCommand('take', function (Message $message, array $args = []) use ($Bot) {
    // Verificar permissão
    if (!hasPermission($message)) {
        $message->reply("somente o administrador tem permissão para usar esse comando.");
        return;
    }

    // Definir o customer_id e o saldo com base no tipo de chat
    if (in_array($message->chat->type, ['supergroup', 'group'])) {
        if (!isset($message->reply_to_message)) {
            $message->reply("Você precisa responder à mensagem de um usuário para usar este comando");
            return;
        }
        $customer_id = $message->reply_to_message->from->id;
        $balance = $args[0] ?? null;
    } else {
        $customer_id = $args[0] ?? null;
        $balance = $args[1] ?? null;
    }

    // Validar entrada
    if (!$customer_id || !is_numeric($balance) || $balance <= 0) {
        $message->reply("Entrada inválida! Forneça um ID de usuário válido e um saldo positivo.");
        return;
    }

    // Verificar se é um bot
    if (isBot($customer_id)) {
        $message->reply("O bot não pode usar este serviço!");
        return;
    }

    // Prevenir que o saldo do criador seja alterado
    if ($customer_id == DEVELOPER_ID) {
        $message->reply("Este não é um cliente, é meu criador!");
        return;
    }

    // Buscar cliente
    $customer = new Customer($customer_id);

    // Verificar se o cliente não possui assinatura ilimitada
    if (!$customer->getIsUnlimited()) {
        $oldBalance = $customer->getCredits();

        // Ajustar saldo
        $newBalance = max(0, $oldBalance - $balance);
        $customer->setCredits($newBalance);

        // Enviar mensagem de sucesso
        $msg  = "<b>SALDO REMOVIDO </b>\n\n";
        $msg .= "SALDO ANTIGO: <code>" . htmlspecialchars(number_format($oldBalance, 0, '', '')) . "</code>\n";
        $msg .= "NOVO SALDO: <code>" . htmlspecialchars(number_format($newBalance, 0, '', '')) . "</code>";
        $message->reply($msg, ['parse_mode' => 'HTML']);

        // Logar a operação
        $logMsg  = "‼️ <b>Admin took Credits from Customer</b> ‼️\n\n";
        $logMsg .= "Taken Credits: <i>{$balance}</i>\n";
        $logMsg .= "Admin: <i>" . htmlspecialchars("[{$message->from->id}]") . "</i>\n";
        $logMsg .= "Customer: <i>" . htmlspecialchars("[{$customer_id}]") . "</i>";
        $Bot->sendMessage(LOGGING_ID, $logMsg, ['parse_mode' => 'HTML']);
    } else {
        $message->reply("This Customer has the Unlimited Subscription!");
    }
});

// Comando Block
$Bot->onCommand('block', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if (!in_array($user->status, ['administrator', 'creator'])) return;

    $customer_id = in_array($message->chat->type, ['supergroup', 'group']) ? $message->reply_to_message->from->id : $args[0];

    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);

    $customer = isValidCustomer($message, $customer_id);
    if (!$customer) return;

    if ($customer->getIsBlocked()) {
        $message->reply("Este cliente já está bloqueado!");
        return;
    }

    $customer->setIsBlocked(true);
    $message->reply("USUARIO BLOQUEADO COM SUCESSO");

    if ($user->status == 'administrator') {
        logAdminAction($user, 'Admin Blocked Customer', $customer_id, $client);
    }
});

// Comando Unblock
$Bot->onCommand('unblock', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if (!in_array($user->status, ['administrator', 'creator'])) return;

    $customer_id = in_array($message->chat->type, ['supergroup', 'group']) ? $message->reply_to_message->from->id : $args[0];

    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);

    $customer = isValidCustomer($message, $customer_id);
    if (!$customer) return;

    if (!$customer->getIsBlocked()) {
        $message->reply("Este cliente já está desbloqueado!");
        return;
    }

    $customer->setIsBlocked(false);
    $message->reply("USUARIO DESBLOQUEADO COM SUCESSO");

    if ($user->status == 'administrator') {
        logAdminAction($user, 'Admin Unblocked Customer', $customer_id, $client);
    }
});

// Comando Unlimited
$Bot->onCommand('unlimited', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if (!in_array($user->status, ['administrator', 'creator'])) return;

    $customer_id = in_array($message->chat->type, ['supergroup', 'group']) ? $message->reply_to_message->from->id : $args[0];

    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);

    $customer = isValidCustomer($message, $customer_id);
    if (!$customer) return;

    if ($customer->getIsUnlimited()) {
        $message->reply("Este cliente já possui assinatura ilimitada!");
        return;
    }

    $customer->setIsUnlimited(true);
    $message->reply("Cliente agora tem assinatura ilimitada");

    if ($user->status == 'administrator') {
        logAdminAction($user, 'Admin gave Customer Unlimited Subscription', $customer_id, $client);
    }
});

// Comando Limited
$Bot->onCommand('limited', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if (!in_array($user->status, ['administrator', 'creator'])) return;

    $customer_id = in_array($message->chat->type, ['supergroup', 'group']) ? $message->reply_to_message->from->id : $args[0];

    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);

    $customer = isValidCustomer($message, $customer_id);
    if (!$customer) return;

    if (!$customer->getIsUnlimited()) {
        $message->reply("Este Cliente não possui Assinatura Ilimitada!");
        return;
    }

    $customer->setIsUnlimited(false);
    $message->reply("O cliente agora tem assinatura limitada");

    if ($user->status == 'administrator') {
        logAdminAction($user, 'Admin took Unlimited Subscription from Customer', $customer_id, $client);
    }
});

$Bot->onCommand('balance', function (Message $message) use ($Bot) {
    $customer_id = $message->from->id;
    $customer = new Customer($customer_id);

    $msg  = "<b>SUAS INFORMACOES:</b>\n\n";
    $msg .= "<b>ID TELEGRAM:</b> " . htmlspecialchars($customer->getTelegramID()) . "\n";
    $msg .= "<b>SALDO:</b> " . htmlspecialchars($customer->getIsUnlimited() ? "ILIMITADO" : number_format($customer->getCredits(), 0, '', '')) . "\n";
    $msg .= "<b>BLOQUEADO?:</b> " . ($customer->getIsBlocked() ? "SIM" : "NAO") . "\n\n";
    $msg .= "<b>SALDO ILIMITADO PERMANENTE:</b> <code>$150 BRL</code>\n";
    $msg .= "<b>ADIQUIRA O SALDO COM :</b> @RHUANCPM\n";
    $msg .= "<b>BOT:</b> @HELP_RH_BOT";
    $message->reply($msg, ['parse_mode' => 'HTML']);
});

$Bot->start();