<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Response</title>
</head>

<body>
    <dl>
        <dt>status</dt>
        <dd>
            <p><?= htmlspecialchars((string)($status), ENT_QUOTES, 'UTF-8') ?>?</p>
        </dd>
    </dl>
</body>

</html>