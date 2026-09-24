<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
\Northstar\Auth::logout();
\Northstar\Response::redirect('/');
