# Test Implementation of reCAPTCHA and hCaptcha

This repository contains examples of integrating and verifying forms using **Google reCAPTCHA** and **hCaptcha**. The objective is to evaluate how both verification systems perform in a testing environment.

## Description

This repository includes two examples of server-side captcha verification:

- **Google reCAPTCHA**:  
  - A PHP file that processes the reCAPTCHA response sent from a form.
  - It sends a POST request to Google's verification endpoint to validate the response.

- **hCaptcha**:  
  - An HTML page that loads the hCaptcha widget and submits the form to a PHP script.
  - A PHP file that utilises cURL to send the hCaptcha response to the verification endpoint and confirm its validity.

## Project Files

### 1. `index.html` (hCaptcha Implementation)

This file contains the basic structure of a web page in Spanish that:
- Asynchronously loads the hCaptcha script from `https://js.hcaptcha.com/1/api.js`.
- Displays a form that submits the response to `validate.php`.
- Includes a `<div>` element with the class `h-captcha` where the hCaptcha widget is rendered.  
  **Note:** Be sure to replace `"YOUR_SITE_KEY"` (or the current key) with the site key provided by hCaptcha.

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Implementación de hCaptcha</title>
  <!-- Load the hCaptcha script -->
  <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
  <!-- Form that sends data to validate.php -->
  <form action="validate.php" method="POST">
    <!-- Remember to replace "YOUR_SITE_KEY" with your site key provided by hCaptcha -->
    <div class="h-captcha" data-sitekey="70c6c7d0-4478-4ddc-93e6-f84ac692e8a0"></div>
    <br>
    <button type="submit">Submit</button>
  </form>
</body>
</html>
```

### 2. `validate.php` (Google reCAPTCHA Validation)

This PHP script handles:

- Verifying that the request to the server is of type **POST**.
- Retrieving the reCAPTCHA response from the form via `$_POST['g-recaptcha-response']`.
- Collecting the user's IP address for additional security.
- Sending a POST request to the endpoint `https://www.google.com/recaptcha/api/siteverify` using `file_get_contents` with a stream context.
- Decoding the JSON response and displaying a message indicating whether the verification was successful or not.

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Secret key provided by Google
    $secret = '6Lc5UvgqAAAAAOJBGO_gEEnTFa7vVYPKQYqv1983';
    
    // Capture the reCAPTCHA response sent from the form
    $response = $_POST['g-recaptcha-response'];
    // Get the user's IP address
    $remoteip = $_SERVER['REMOTE_ADDR'];
    
    // Data for verification
    $data = [
        'secret'   => $secret,
        'response' => $response,
        'remoteip' => $remoteip
    ];
    
    // Prepare the POST request
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
    
    // Decode the JSON response
    $resultJson = json_decode($result);
    print_r($result);
    
    if ($resultJson->success !== true) {
        // If verification fails
        echo 'reCAPTCHA verification failed. Please try again.';
    } else {
        // If verification is successful, process the form
        echo 'reCAPTCHA verified successfully. Form processed!';
        // You can add additional form data processing here.
    }
}
?>
```

### 3. `hcaptcha_validate.php` (hCaptcha Validation)

This PHP script processes the response from the hCaptcha widget as follows:

- It verifies that the request is of type **POST**.
- Retrieves the token sent by the widget via `$_POST['h-captcha-response']`.
- Defines the hCaptcha secret key.
- Prepares the data (including the user's IP address) and sends it to the endpoint `https://hcaptcha.com/siteverify` using **cURL**.
- Decodes the JSON response and displays a message indicating whether the verification was successful or if there was an error.

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the token sent by the hCaptcha widget
    $token = $_POST['h-captcha-response'];
    
    // hCaptcha secret key (provided by hCaptcha)
    $secret = "ES_2634a86f734a4b1eb4e200a53c3cb15f";

    // Prepare the information to send via POST
    $data = [
        'secret'   => $secret,
        'response' => $token,
        // Optional: include the user's IP address for added security
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    // Use cURL to send the POST request to hCaptcha
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the JSON response
    $result = json_decode($response, true);

    // Check if the validation was successful
    if ($result['success']) {
        echo "hCaptcha verified successfully. You can proceed with processing your form.";
        // You can continue processing other form data here.
    } else {
        echo "Error in hCaptcha verification. Please try again.";
    }
} else {
    echo "Invalid access.";
}
?>
```

## Setup and Usage

1. **Clone the repository:**  
   Download or clone this repository to your local environment or web server.

2. **Update Keys:**  
   Replace the site keys and secret keys used in the scripts:
   - For Google reCAPTCHA: update the `$secret` variable and ensure that the form field name `g-recaptcha-response` is correct.
   - For hCaptcha: update the key in the `data-sitekey` attribute of the HTML file and the `$secret` variable in `hcaptcha_validate.php`.

3. **Configure the PHP Environment:**  
   Ensure that your PHP environment allows outgoing requests to perform the verification (e.g., via `file_get_contents` or cURL).

4. **Testing:**  
   - Open the `index.html` file to view the form and test the hCaptcha implementation.
   - Submit the form and check the verification message.
   - For testing reCAPTCHA, ensure you have a form that sends the response to the `validate.php` script.

## Security Considerations

- **Testing Environment:** These examples are intended solely for testing purposes.
- **Secret Keys:** Do not publish your secret keys in public repositories.
- **Additional Validations:** In a production environment, implement further validations and error handling to ensure system security and integrity.

## Contributions

Improvements and contributions are welcome. If you wish to add features or correct any issues, please open an *issue* or submit a *pull request*.

## Licence

This project is distributed under the MIT licence. See the `LICENSE` file for further details.

---

# Prueba de Implementación de reCAPTCHA y hCaptcha

Este repositorio contiene ejemplos de integración y verificación de formularios utilizando **reCAPTCHA de Google** y **hCaptcha**. El objetivo es evaluar cómo funcionan ambos sistemas de verificación en un entorno de prueba.

## Descripción

El repositorio incluye dos ejemplos de validación de captcha en el lado del servidor:

- **Google reCAPTCHA**:  
  - Archivo PHP que procesa la respuesta del reCAPTCHA enviado por un formulario.
  - Envía una solicitud POST al endpoint de verificación de Google para validar la respuesta.

- **hCaptcha**:  
  - Página HTML que carga el widget de hCaptcha y envía el formulario a un script PHP.
  - Archivo PHP que utiliza cURL para enviar la respuesta del hCaptcha al endpoint de verificación y confirmar su validez.

## Archivos del Proyecto

### 1. `index.html` (Implementación de hCaptcha)

Este archivo contiene la estructura básica de una página web en español que:
- Carga asíncronamente el script de hCaptcha desde `https://js.hcaptcha.com/1/api.js`.
- Muestra un formulario que envía la respuesta a `validate.php`.
- Incluye un elemento `<div>` con la clase `h-captcha` donde se renderiza el widget de hCaptcha.  
  **Nota:** Asegúrate de reemplazar `"YOUR_SITE_KEY"` (o la clave actual) por la clave de sitio que te haya proporcionado hCaptcha.

```html
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Implementación de hCaptcha</title>
  <!-- Carga del script de hCaptcha -->
  <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
  <!-- Formulario que envía los datos a validate.php -->
  <form action="validate.php" method="POST">
    <!-- Recuerda reemplazar "YOUR_SITE_KEY" con tu clave de sitio proporcionada por hCaptcha -->
    <div class="h-captcha" data-sitekey="70c6c7d0-4478-4ddc-93e6-f84ac692e8a0"></div>
    <br>
    <button type="submit">Enviar</button>
  </form>
</body>
</html>
```

### 2. `validate.php` (Validación de Google reCAPTCHA)

Este script PHP se encarga de:

- Verificar que la solicitud al servidor sea de tipo **POST**.
- Obtener la respuesta enviada por el widget de reCAPTCHA a través de `$_POST['g-recaptcha-response']`.
- Recoger la dirección IP del usuario para mayor seguridad.
- Enviar una solicitud POST al endpoint `https://www.google.com/recaptcha/api/siteverify` utilizando `file_get_contents` y un contexto de flujo.
- Decodificar la respuesta JSON y mostrar un mensaje indicando si la verificación fue exitosa o falló.

```php
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
```

### 3. `hcaptcha_validate.php` (Validación de hCaptcha)

Este script PHP procesa la respuesta del widget de hCaptcha de la siguiente manera:

- Verifica que la solicitud sea de tipo **POST**.
- Obtiene el token enviado por el widget mediante `$_POST['h-captcha-response']`.
- Define la clave secreta de hCaptcha.
- Prepara los datos (incluyendo la IP del usuario) y los envía al endpoint `https://hcaptcha.com/siteverify` utilizando **cURL**.
- Decodifica la respuesta JSON y muestra un mensaje de verificación exitosa o de error, según corresponda.

```php
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
```

## Configuración y Uso

1. **Clonar el repositorio:**  
   Descarga o clona este repositorio en tu entorno local o servidor web.

2. **Actualizar claves:**  
   Reemplaza las claves de sitio y las claves secretas utilizadas en los scripts:
   - Para reCAPTCHA de Google: reemplaza la variable `$secret` y asegúrate de que el campo `g-recaptcha-response` tenga el nombre correcto en el formulario.
   - Para hCaptcha: actualiza la clave en el atributo `data-sitekey` del HTML y la variable `$secret` en `hcaptcha_validate.php`.

3. **Configurar entorno PHP:**  
   Asegúrate de que tu entorno de PHP permite solicitudes salientes para realizar la verificación (por ejemplo, a través de `file_get_contents` o cURL).

4. **Pruebas:**  
   - Accede al archivo `index.html` para ver el formulario y probar la implementación de hCaptcha.
   - Envía el formulario y revisa el mensaje de verificación.
   - Para probar reCAPTCHA, asegúrate de tener un formulario que envíe la respuesta al script `validate.php`.

## Consideraciones de Seguridad

- **Ambiente de Pruebas:** Estos ejemplos están destinados únicamente para fines de prueba.  
- **Claves Secretas:** No publiques tus claves secretas en repositorios públicos.  
- **Validaciones Adicionales:** En un entorno de producción, implementa validaciones y manejo de errores adicionales para asegurar la integridad y seguridad del sistema.

## Contribuciones

Las mejoras y contribuciones son bienvenidas. Si deseas agregar funcionalidades o corregir algún problema, por favor, abre un *issue* o envía un *pull request*.

## Licencia

Este proyecto se distribuye bajo la licencia MIT. Consulta el archivo `LICENSE` para más detalles.
