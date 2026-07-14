<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de Despacho - DiCreme</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #4A5568;">¡Hola, {{ $cliente->nombre_empresa ?? 'Distribuidor' }}!</h2>
    <p>Te informamos que tu pedido en el sistema <strong>DiCreme</strong> ya está en proceso de despacho y va en camino a tu dirección.</p>
    
    <div style="background-color: #F7FAFC; border-left: 4px solid #3182ce; padding: 15px; margin: 20px 0;">
        <p style="margin: 0 0 8px 0;"><strong>Detalles del Despacho:</strong></p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #4A5568;">
            <li><strong>ID Despacho:</strong> #{{ $despacho->id }}</li>
            @if(!empty($despacho->direccion_entrega))
                <li><strong>Dirección de entrega:</strong> {{ $despacho->direccion_entrega }} ({{ $despacho->comuna }})</li>
            @endif
            @if(!empty($despacho->fecha_entrega))
                <li><strong>Fecha estimada de entrega:</strong> {{ \Carbon\Carbon::parse($despacho->fecha_entrega)->format('d/m/Y') }}</li>
            @endif
            @if(!empty($despacho->id_usuario_dicreme))
                <li><strong>Despachador:</strong> {{ $despacho->id_usuario_dicreme }}</li>
            @endif
        </ul>
    </div>

    <p>Puedes ver el estado o hacer seguimiento de tu pedido haciendo clic en el siguiente botón:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="background-color: #3182ce; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
            Ver Seguimiento del Pedido
        </a>
    </div>

    <p style="font-size: 12px; color: #718096;">Si el botón no funciona, puedes copiar y pegar este enlace en tu navegador:</p>
    <p style="font-size: 12px; color: #3182ce; word-break: break-all;">{{ $url }}</p>
    
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
    <p style="font-size: 12px; color: #a0aec0;">Gracias por confiar en DiCreme. Este es un correo generado automáticamente por el sistema.</p>
</body>
</html>
