<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se obtiene el token enviado por el widget hCaptcha
    $token = $_POST['h-captcha-response'];
    
    // Clave secreta de hCaptcha (proporcionada por hCaptcha)
    $secret = "ES_2634a86f734a4b1eb4e200a53c3cb15f";

    // Se prepara la información para enviar en el POST
    $data = [
        'secret'   => $secret,
        'response' => $token,
        // Opcional: agregar la IP del usuario para mayor seguridad
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    // Se utiliza cURL para enviar la solicitud POST a hCaptcha
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);

    // Se decodifica la respuesta en formato JSON
    $result = json_decode($response, true);

    // Se verifica si la validación fue exitosa
    if ($result['success']) {
        echo "hCaptcha verificado correctamente. Puedes continuar con el procesamiento de tu formulario.";
        // Aquí puedes continuar procesando otros datos del formulario
    } else {
        echo "Error en la verificación de hCaptcha. Por favor, inténtalo nuevamente.";
    }
} else {
    echo "Acceso inválido.";
}
?>
