// Importar librerías
const express = require('express');
const bodyParser = require('body-parser');
const nodemailer = require('nodemailer');

const app = express();
const PORT = 3000;

// --- ?? 1. CONFIGURACIÓN CRÍTICA DE GMAIL Y SEGURIDAD ?? ---
// REEMPLAZA ESTOS VALORES con tus credenciales de Gmail.
// IMPORTANTE: Debes usar una Contraseña de Aplicación (App Password) de Google, NO tu contraseña de Gmail normal.
const GMAIL_USER = 'tu_correo_de_envio@gmail.com'; 
const GMAIL_APP_PASSWORD = 'TU_CONTRASEÑA_DE_APLICACION_DE_GOOGLE'; 

// Configuración de Nodemailer (usa el servicio SMTP de Gmail)
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: GMAIL_USER,
        pass: GMAIL_APP_PASSWORD
    }
});

// Almacenamiento temporal para OTPs (En producción se recomienda usar Redis o una base de datos)
const otps = {}; 

// --- 2. CONFIGURACIÓN DEL SERVIDOR ---
app.use(bodyParser.json());
// Esta línea permite que tu index.html acceda al servidor si están en la misma carpeta o en una carpeta 'public'
app.use(express.static('./')); 

// --- 3. ENDPOINT para SOLICITAR y ENVIAR el código por CORREO (/api/send-otp) ---
app.post('/api/send-otp', (req, res) => {
    const { identifier } = req.body; // El 'identifier' es el correo electrónico
    const email = identifier; 
    
    // 1. Generar un código OTP de 6 dígitos
    const otpCode = Math.floor(100000 + Math.random() * 900000).toString();
    
    // 2. Almacenar el código y su tiempo de expiración (5 minutos)
    otps[email] = { 
        code: otpCode, 
        expires: Date.now() + 5 * 60 * 1000 
    };

    // 3. Opciones del correo
    const mailOptions = {
        from: GMAIL_USER,
        to: email, 
        subject: 'Clave de Acceso Única (OTP) para tu Web',
        html: `
            <h2>Tu Clave de Acceso Única (OTP)</h2>
            <p>Hola,</p>
            <p>Usaste este correo para solicitar una clave de acceso. Tu código es:</p>
            <h1 style="color: #007bff; background-color: #f0f0f0; padding: 10px; border-radius: 5px; text-align: center;">${otpCode}</h1>
            <p>Este código expira en 5 minutos.</p>
            <p>Si no solicitaste este código, ignora este correo.</p>
        `
    };

    // 4. Enviar el correo
    transporter.sendMail(mailOptions, (error, info) => {
        if (error) {
            console.error('Error al enviar correo:', error);
            // Envía una respuesta 500 para indicar un fallo en el servidor
            return res.status(500).json({ success: false, message: 'Error al enviar el correo. Revisa la configuración de Nodemailer.' });
        }
        console.log('Correo enviado: ' + info.response);
        res.json({ success: true, message: 'Clave de acceso enviada al correo electrónico.' });
    });
});

// --- 4. ENDPOINT para VERIFICAR el código (/api/verify-otp) ---
app.post('/api/verify-otp', (req, res) => {
    const { identifier, otp } = req.body;
    const storedOtp = otps[identifier];

    // Verificar si el código existe y coincide
    if (!storedOtp || storedOtp.code !== otp) {
        return res.status(401).json({ success: false, message: 'Código incorrecto.' });
    }

    // Verificar si el código ha expirado
    if (Date.now() > storedOtp.expires) {
        delete otps[identifier]; 
        return res.status(401).json({ success: false, message: 'El código ha expirado.' });
    }

    // Código válido: Eliminar de la lista para asegurar que sea de un solo uso
    delete otps[identifier];

    res.json({ success: true, message: 'Acceso verificado correctamente.' });
});

// Iniciar el servidor
app.listen(PORT, () => {
    console.log(`Servidor de autenticación iniciado en http://localhost:${PORT}`);
});