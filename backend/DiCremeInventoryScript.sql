-- 1. Tablas Base (Sin llaves foráneas externas)
CREATE TABLE usuario_dicreme (
    id_usuario_dicreme SERIAL PRIMARY KEY,
    nombre_usuario VARCHAR(30) NOT NULL,
    contrasena VARCHAR(255) NOT NULL, -- Aumentado para seguridad
    correo VARCHAR(50) NOT NULL
);

CREATE TABLE usuario_distribuidores (
    id_usuario_distribuidor SERIAL PRIMARY KEY,
    nombre_usuario VARCHAR(30) NOT NULL,
    contrasena VARCHAR(255) NOT NULL, -- Aumentado para seguridad
    direccion VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(50) NOT NULL,
    comuna VARCHAR(30) NOT NULL,
    rut_empresa VARCHAR(30) NOT NULL
);

-- 2. Productos y Stock (Relación corregida)
CREATE TABLE producto (
    id_producto SERIAL PRIMARY KEY,
    nombre_producto VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    tipo_litraje VARCHAR(50) NOT NULL,
    lugar_de_guardado VARCHAR(100) NOT NULL,
    precio INT NOT NULL
    -- Se elimina la referencia a stock aquí para evitar circularidad
);

CREATE TABLE stock (
    id_stock SERIAL PRIMARY KEY,
    id_producto INT UNIQUE REFERENCES producto(id_producto) ON DELETE CASCADE,
    cantidad INT NOT NULL DEFAULT 0
);

-- 3. Pedidos y Relaciones
CREATE TABLE pedido (
    id_pedido SERIAL PRIMARY KEY,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rol (
    id_rol SERIAL PRIMARY KEY,
    id_usuario INT REFERENCES usuario_dicreme(id_usuario_dicreme),
    nombre_rol VARCHAR(40)
);

CREATE TABLE despacho (
    id_despacho SERIAL PRIMARY KEY,
    id_pedido INT REFERENCES pedido(id_pedido),
    id_usuario_distribuidores INT REFERENCES usuario_distribuidores(id_usuario_distribuidor),
    direccion_entrega VARCHAR(255),
    persona_receptora VARCHAR(40),
    fecha_de_entrega DATE
);

CREATE TABLE estado (
    id_estado SERIAL PRIMARY KEY,
    id_pedido INT REFERENCES pedido(id_pedido),
    nombre_estado VARCHAR(40),
    usuario_modifico VARCHAR(40)
);

CREATE TABLE pedido_producto ( 
    id_producto INT REFERENCES producto(id_producto),
    id_pedido INT REFERENCES pedido(id_pedido),
    PRIMARY KEY (id_producto, id_pedido)
);

CREATE TABLE venta (
    id_venta SERIAL PRIMARY KEY,
    id_pedido INT REFERENCES pedido(id_pedido),
    fecha DATE DEFAULT CURRENT_DATE,
    numero_factura INT,
    estado_pago VARCHAR(40),
    cantidad_productos INT,
    tipo_producto VARCHAR(40),
    codigo_venta INT,
    monto_total INT
);