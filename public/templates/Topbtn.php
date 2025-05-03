       <!-- Back to Top Button -->
   <button id="backToTop" class="tunisian-scroll-top" style="display: none;">
       <i class="fas fa-chevron-up"></i>
   </button>
   <!-- Scripts -->
   <script>
   // Back to Top Button Script
   window.addEventListener('scroll', function() {
       var backToTopButton = document.getElementById('backToTop');
       if (window.pageYOffset > 300) {
           backToTopButton.style.display = 'flex';
       } else {
           backToTopButton.style.display = 'none';
       }
   });
   document.getElementById('backToTop').addEventListener('click', function() {
       window.scrollTo({ top: 0, behavior: 'smooth' });
   });
   </script>
   
  