
    </main>
    <footer class="footer mt-auto py-2" style="background: #000211ff; border-top: 0;">
    <div class="container text-center">
        <span class="fw-semibold" style="color: #fff; font-size: 0.95rem;">
            Sistema de Compras -  &nbsp;|&nbsp; Solución profesional para gestión de compras  &nbsp;|&nbsp; © 2025 Todos los derechos reservados
        </span>
    </div>
</footer>
    <!-- jQuery (requerido por Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- JS personalizado -->
    <?php if (!defined('ASSETS_URL')) { require_once __DIR__ . '/config.php'; } ?>
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            // Efecto smooth scroll para enlaces internos
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>