document.addEventListener('DOMContentLoaded', function() {
    // Efecto de carga gradual
    const elementsToAnimate = document.querySelectorAll('.card, .stat-card');
    elementsToAnimate.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            el.style.opacity = '1';
        }, index * 100);
    });

    // Tooltips minimalistas
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width/2 - tooltip.offsetWidth/2}px`;
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;
            
            this.addEventListener('mouseleave', () => {
                tooltip.remove();
            });
        });
    });

    // Actualización de notificaciones (versión mejorada)
    const updateNotifications = async () => {
        try {
            const response = await fetch('includes/update_notifications.php');
            const data = await response.json();
            
            // Actualizar contadores
            document.querySelectorAll('[data-notification="messages"]').forEach(el => {
                el.textContent = data.unread_messages || '';
                el.style.display = data.unread_messages > 0 ? 'flex' : 'none';
            });
            
            document.querySelectorAll('[data-notification="matches"]').forEach(el => {
                el.textContent = data.pending_matches || '';
                el.style.display = data.pending_matches > 0 ? 'flex' : 'none';
            });
            
        } catch (error) {
            console.error('Error updating notifications:', error);
        }
    };
    
    // Actualizar cada 30 segundos
    setInterval(updateNotifications, 30000);
    updateNotifications();

    // Efecto hover sutil para tarjetas
    const cards = document.querySelectorAll('.card, .stat-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
});

// Función para mostrar toast
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => toast.remove(), 5000);
}