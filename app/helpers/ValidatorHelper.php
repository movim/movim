<?php

use Respect\Validation\Validator;

function validateServerNode($server, $node)
{
    return validateServer($server) && validateNode($node);
}

function validateServer($server)
{
    return (Validator::stringType()->noWhitespace()->length(6, 64)->validate($server));
}

function validateNode($node)
{
    return (Validator::stringType()->length(2, 256)->validate($node));
}

function validateTag($tag)
{
    return Validator::stringType()->notEmpty()->validate($tag);
}

function validateJid($jid)
{
    return (Validator::stringType()->noWhitespace()->length(6, 256)->validate($jid));
}

function validateRoom($room)
{
    return (Validator::stringType()->noWhitespace()->contains('@')->length(6, 256)->validate($room));
}

function validateForm($data)
{
    $l = Movim\i18n\Locale::start();

    return Validator::in(array_keys($l->getList()))->validate($data->language->value);
}