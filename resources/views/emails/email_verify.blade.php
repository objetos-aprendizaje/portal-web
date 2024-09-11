<!DOCTYPE html>
<html lang="es">
<head>
    <title>Restablecer contraseña</title>
</head>
<body>
    <h1>Hola,</h1>
    <p>Has recibido este correo electrónico porque hemos recibido una solicitud de registro.</p>
    <p>Haz clic en el botón de abajo para verificar su cuenta de correo electrónico:</p>
    <a href="{{ $parameters['url'] }}&token={{ $parameters['token'] }}" style="background-color: {{app('general_options')['color_1']}}; color: white; padding: 10px 20px; text-decoration: none;">Verificar cuenta de correo electrónico</a>
    <p>Si no creaste una cuenta, no se requiere ninguna acción adicional.</p>
    <p>Gracias,</p>
    @if ($general_options['commercial_name'] != '')
        <p>El equipo de {{ $general_options['commercial_name'] }}</p>
    @endif
</body>
</html>
