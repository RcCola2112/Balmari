        </main>
    </div>
    
    <!-- Footer Scripts -->
    <script>
        // Mark current nav link as active
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
            document.querySelectorAll('.nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage || (currentPage === '' && href === 'dashboard.php')) {
                    link.classList.add('active', 'bg-[#715A5A]');
                }
            });
        });
        
        // Flash message auto-dismiss
        document.querySelectorAll('[data-flash]').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>
