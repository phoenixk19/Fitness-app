<?php
// File: dashboard/editor/includes/footer.php
?>
    </div>
    <button class="theme-toggle" id="themeToggle"><i class="bi bi-moon-fill" id="themeIcon"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const themeToggle = document.getElementById('themeToggle'), themeIcon = document.getElementById('themeIcon'), html = document.documentElement;
        const saved = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        html.setAttribute('data-bs-theme', saved);
        function updateIcon(t) { themeIcon.className = t === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill'; }
        updateIcon(saved);
        themeToggle.onclick = () => { const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light'; html.setAttribute('data-bs-theme', next); localStorage.setItem('theme', next); updateIcon(next); };
        const menuToggle = document.getElementById('menuToggle'), sidebar = document.getElementById('editorSidebar'), overlay = document.getElementById('sidebarOverlay');
        function closeSidebar() { if(sidebar) sidebar.classList.remove('open'); if(overlay) overlay.classList.remove('active'); }
        function openSidebar() { if(sidebar) sidebar.classList.add('open'); if(overlay) overlay.classList.add('active'); }
        if(menuToggle) menuToggle.addEventListener('click', (e) => { e.stopPropagation(); sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
        if(overlay) overlay.addEventListener('click', closeSidebar);
        window.addEventListener('resize', () => { if(window.innerWidth > 992) closeSidebar(); });
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeSidebar(); });
    </script>
    <?php if (isset($page_specific_scripts)) echo $page_specific_scripts; ?>
</body>
</html>