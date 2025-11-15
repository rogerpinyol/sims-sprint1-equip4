<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Error</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:40px}</style>
</head>
<body>
  <h1>Error</h1>
  <p><?= htmlspecialchars($errorMessage ?? 'Unknown error') ?></p>
</body>
</html>
