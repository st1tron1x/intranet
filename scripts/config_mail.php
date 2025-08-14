<?php
// scripts/config_mail.php
return [
    // Configuración SMTP
    'host' => 'smtp.gmail.com', // o tu servidor SMTP
    'username' => 'intranet@correagro.com',
    'password' => 'tu_password_aqui', // Usar App Password para Gmail
    'port' => 587,
    'secure' => 'tls', // 'ssl' para puerto 465
    'from_name' => 'Intranet Correagro',
    
    // Configuración adicional
    'timeout' => 30,
    'debug' => 0, // 0 = off, 1 = client messages, 2 = client and server messages
    
    // Destinatarios por defecto
    'admin_emails' => [
        'admin@correagro.com',
        'rrhh@correagro.com'
    ],
    
    // Plantillas de correo
    'templates' => [
        'solicitud_creada' => [
            'subject' => 'Nueva Solicitud - {{tipo}}',
            'body' => '
                <h2>Nueva Solicitud Registrada</h2>
                <p><strong>Usuario:</strong> {{usuario}}</p>
                <p><strong>Tipo:</strong> {{tipo}}</p>
                <p><strong>Descripción:</strong> {{descripcion}}</p>
                <p><strong>Fecha:</strong> {{fecha}}</p>
                <hr>
                <p><em>Este es un mensaje automático del sistema de intranet.</em></p>
            '
        ],
        'solicitud_aprobada' => [
            'subject' => 'Solicitud Aprobada - {{tipo}}',
            'body' => '
                <h2>Su Solicitud ha sido Aprobada</h2>
                <p>Estimado/a {{usuario}},</p>
                <p>Su solicitud de <strong>{{tipo}}</strong> ha sido aprobada.</p>
                <p><strong>Comentarios:</strong> {{comentarios}}</p>
                <hr>
                <p><em>Sistema de Intranet Correagro</em></p>
            '
        ],
        'documento_subido' => [
            'subject' => 'Nuevo Documento Disponible - {{titulo}}',
            'body' => '
                <h2>Nuevo Documento Disponible</h2>
                <p><strong>Título:</strong> {{titulo}}</p>
                <p><strong>Categoría:</strong> {{categoria}}</p>
                <p><strong>Descripción:</strong> {{descripcion}}</p>
                <p><a href="{{url}}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ver Documento</a></p>
                <hr>
                <p><em>Sistema de Intranet Correagro</em></p>
            '
        ]
    ]
];
?>