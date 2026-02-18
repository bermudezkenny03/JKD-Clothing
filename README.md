# Arquitectura del sistema:JKD-Clothing
JKD Clothing es una plataforma  de e-commerce desarrollada con arquitectura distribuida, diseñada para ofrecer alta disponibilidad, escalabilidad y una experiencia de compra rápida y segura.
# Problema que resuelve
JKD Clothing resuelve el problema de vender ropa y calzado de forma rápida, segura y sin interrupciones, evitando caídas del sistema, lentitud en la carga y dificultades para atender a muchos usuarios al mismo tiempo, Gracias a su arquitectura distribuida, permite que la tienda esté siempre disponible, sea fácil de escalar cuando crecen las visitas y ofrezca una mejor experiencia de compra para los clientes.
## Servicios del sistemas
El sistema ofrece servicios como el registro e inicio de sesión de usuarios, la visualización del catálogo de ropa y calzado, el carrito de compras, la gestión de pedidos y un módulo de administración para crear, editar y eliminar productos, así como actualizar precios e inventario,cada uno de estos procesos es independiente, ya que el catálogo puede funcionar sin el carrito, el carrito sin generar un pedido, y la administración funciona aparte del proceso de compra del usuario. 
## Comunicacion entre servicios 
Los servicios se comunican entre sí mediante peticiones HTTP usando APIs, lo que permite que cada servicio envíe y reciba información, como datos de productos, usuarios y pedidos, sin depender directamente de los demás.
## Tipo de arquitectura
La plataforma utiliza una arquitectura cliente servidor, porque los usuarios acceden a la tienda desde un navegador (cliente) y todas las operaciones importantes del sistema, como el inicio de sesión, la consulta de productos, el registro de pedidos y el acceso a la base de datos, se realizan en un servidor central.
## Base de datos
Se utiliza una base de datos centralizada donde se almacena la información de los usuarios, los productos, los pedidos, los precios y las imágenes. Esta base de datos puede ser relacional, por ejemplo MySQL, conectada al backend en PHP.
## Usuarios del sitema
Los usuarios del sistema son los clientes que ingresan a la tienda para consultar productos y realizar compras, y el administrador, que se encarga de gestionar los productos, los pedidos y la información general de la tienda.
## Riesgos y fallas posibles 
Entre los principales riesgos se encuentran la caída de alguno de los servicios, problemas de conexión con la base de datos, lentitud cuando hay muchos usuarios conectados al mismo tiempo, errores en la comunicación entre servicios y posibles fallas de seguridad si no se protegen correctamente los accesos y la información.

