<?php use JSONWT\JWT;

// crypto.getRandomValues(new Uint8Array(43)).toBase64()

function getJSONWT(): JWT
{
    return new JWT(getDBSettings()['jwts']);
}
