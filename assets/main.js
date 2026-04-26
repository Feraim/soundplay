// =========================================================================
// SCRIPT PRINCIPAL DE INTERACTIVIDAD (JAVASCRIPT)
// Maneja peticiones del lado del cliente y la UI sin recargas
// =========================================================================

// Función maestra para navegar por páginas vía AJAX sin recargar el DOM
const navegar = async (page) => {
    try {
        const response = await fetch(`src/vista/${page}.php`);
        const content = await response.text();
        
        // Reemplaza el hilo central
        document.getElementById('contenedor-app').innerHTML = content;
        
        // Actualiza el estado iluminado en la botonera de tu pie móvil
        document.querySelectorAll('.item-nav').forEach(item => item.classList.remove('activo'));
        event.currentTarget.classList.add('activo');
        
        // Cambiamos sutilmente la barra de direcciones superior (Clean URL UX)
        window.history.pushState({}, '', `?page=${page}`);
        
        // Para rutas dinámicas que requieren post-load scripts (Como el índice principal)
        if(page === 'inicio') {
            loadInicio();
        }
    } catch(err) {
        console.error('Error cargando la vista: ', err);
    }
};

// =========================================================================
// SISTEMA DE MENÚ DESLIZANTE GESTUAL Y DE CLIC
// =========================================================================
const toggleMenu = () => {
    const menu = document.getElementById('menu-deslizante');
    const overlay = document.getElementById('capa-oscura-menu');
    
    // Conmuta la clase 'abierto' en tu Drawer izquierdo
    menu.classList.toggle('abierto');
    // Conmuta la opacidad visual del fondo cristal que difumina el fondo
    overlay.classList.toggle('visible');
};