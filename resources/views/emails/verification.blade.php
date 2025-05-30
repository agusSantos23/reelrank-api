<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
  </head>

  <body>
    <p>Hello {{ $user->name }},</p>
    <p>Your verification code is: <b>{{ $code }}</b></p>
    <p>Please use this code to verify your email address.</p>
  </body>

</html>