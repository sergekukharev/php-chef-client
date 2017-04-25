<?php

namespace Sergekukharev\PhpChefClient;

interface StatusCode
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;

    const NOT_AUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const CONFLICT = 409;
    const GONE = 410;
    const PRECONDITION_FAILED = 412;
    const PAYLOAD_TOO_LARGE = 413;

    const INTERNAL_SERVER_ERROR = 500;
}
