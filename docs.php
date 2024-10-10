<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <style>
        /* ... (keep existing styles) ... */
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky">
                    <h5 class="sidebar-title">API Endpoints</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="coupons.php">Coupon</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">Products</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-8 px-md-4">
                <h1 class="mt-4">API Documentation</h1>
                
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'coupons';
                switch($page) {
                    case 'coupons':
                        include 'pages/coupons.php';
                        break;
                    case 'users':
                        include 'pages/users.php';
                        break;
                    case 'products':
                        include 'pages/products.php';
                        break;
                    default:
                        include 'pages/coupons.php';
                }
                ?>
            </main>

            <aside class="col-lg-2 d-none d-lg-block content-list">
                <h5 class="mt-4">Contents</h5>
                <ul class="list-unstyled" id="content-list">
                    <!-- This will be populated dynamically based on the current page -->
                </ul>
            </aside>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        hljs.highlightAll();

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Populate and highlight active section in sidebar
        function updateContentList() {
            const contentList = document.getElementById('content-list');
            contentList.innerHTML = '';
            document.querySelectorAll('main section').forEach(section => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = '#' + section.id;
                a.textContent = section.querySelector('h2').textContent;
                li.appendChild(a);
                contentList.appendChild(li);
            });
        }

        // Highlight active section in sidebar
        function highlightActiveSection() {
            let current = '';
            document.querySelectorAll('main section').forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 60) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('#content-list a').forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('href') === '#' + current) {
                    a.classList.add('active');
                }
            });
        }

        // Update content list when page loads
        window.addEventListener('load', updateContentList);

        // Highlight active section on scroll
        window.addEventListener('scroll', highlightActiveSection);

        // Update active nav link
        const currentPage = '<?php echo $page; ?>';
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(currentPage)) {
                link.classList.add('active');
            }
        });
    </script>
</body>

</html>