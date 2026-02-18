# Arquitectura del sistema:JKD-Clothing
JKD Clothing es una plataforma de e-commerce desarrollada con arquitectura distribuida, diseñada para ofrecer alta disponibilidad, escalabilidad y una experiencia de compra rápida y segura.
# Problema que resuelve
JKD Clothing resuelve el problema de vender ropa y calzado de forma rápida, segura y sin interrupciones, evitando caídas del sistema, lentitud en la carga y dificultades para atender a muchos usuarios al mismo tiempo, Gracias a su arquitectura distribuida, permite que la tienda esté siempre disponible, sea fácil de escalar cuando crecen las visitas y ofrezca una mejor experiencia de compra para los clientes.
## Servicios del sistemas
El sistema ofrece servicios como el registro e inicio de sesión de usuarios, la visualización del catálogo de ropa y calzado, el carrito de compras, la gestión de pedidos y un módulo de administración para crear, editar y eliminar productos, así como actualizar precios e inventario.
## Comunicacion entre servicios 
Los servicios se comunican entre sí mediante peticiones HTTP usando APIs, lo que permite que cada servicio envíe y reciba información, como datos de productos, usuarios y pedidos, sin depender directamente de los demás.
