<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clave secreta proporcionada por Google
    $secret = '6Lc5UvgqAAAAAOJBGO_gEEnTFa7vVYPKQYqv1983';
    
    // Captura la respuesta del reCAPTCHA enviada por el formulario
    $response = $_POST['g-recaptcha-response'];
    // Obtiene la IP del usuario
    $remoteip = $_SERVER['REMOTE_ADDR'];
    
    // Datos para la verificación
    $data = [
        'secret'   => $secret,
        'response' => $response,
        'remoteip' => $remoteip
    ];
    
    // Prepara la solicitud POST
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    // Decodifica la respuesta JSON
    $resultJson = json_decode($result);
    print_r($result);
    
    if ($resultJson->success !== true) {
        // Si la verificación falla
        echo 'Verificación de reCAPTCHA fallida. Por favor, inténtalo de nuevo.';
    } else {
        // Si la verificación es exitosa, se procesa el formulario
        echo 'Verificación de reCAPTCHA exitosa. ¡Formulario procesado!';
        // Aquí puedes agregar el procesamiento adicional de los datos del formulario.
    }
}
?>
