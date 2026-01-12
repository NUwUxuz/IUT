<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En Construction</title>
</head>
<body>
    <nav class="desktop-element">
        <?php include_once 'nav.php' ?>
    </nav>
    <div class="phone-element">
        <?php include_once 'header.php' ?>
    </div>
    
    <main>
        <div class="main-content">
            <img src="../images/imagesReferences/en_construction.png" alt="Site en construction">
        </div>
    </main>
    

    <footer class="phone-element">
        <?php include_once'footerNav.php' ?>   
    </footer>
    
</body>
<style>
    body {
        display: flex;
        flex-direction: column; /* Ensure stacking vertically by default */
        justify-content: center;
        align-items: center;
    }

    @media screen and (max-width: 800px) {
        body {
            flex-direction: column;
        }

        .phone-element{
            width: 100%;
        }

        .main-content img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .desktop-element {
            display: none;
        }
    }

    @media screen and (min-width: 801px) {
        body {
            flex-direction: row; /* Optionally switch to row for desktop */
        }

        .main-content img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .phone-element {
            display: none;
        }
    }
</style>
</html>
