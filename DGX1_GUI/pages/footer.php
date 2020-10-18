    <script src="/js/thirdparty/materialize.js"></script>
    
    <div id="overlay"></div>
    <div id="spinner"></div>

    <script src="/js/spinner.js"></script>
    <footer>
        <script>
            $(document).ready(function() {
                $('.dropdown-trigger').dropdown({coverTrigger: false});
                $('.sidenav').sidenav();
                hideSpinner();
            });
        </script>
    </footer>
</body>

</html>
