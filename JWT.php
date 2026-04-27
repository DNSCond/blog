<?php use JSONWT\JWT;

function getJSONWT(): JWT
{
    return new JWT(getDBSettings()['jwts']);
}
