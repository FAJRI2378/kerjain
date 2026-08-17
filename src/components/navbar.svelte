<script>
    import placeholder_logo from "../images/furinanerd-placeholder.jpg"
</script>
<div class="navbar">
    <div class="navbar-sections">
        <img src={placeholder_logo} alt="furinanerd">
    </div>
    <div class="navbar-sections navbar-links">
        <a class="navbar-links-entry" href="./"><h3>Home</h3></a>
        <a class="navbar-links-entry" href="./"><h3>About</h3></a>
        <a class="navbar-links-entry" href="./"><h3>Search</h3></a>
    </div>
    <div class="navbar-sections"><h3 class="navbar-links-entry">Gabung</h3></div>
</div>

<style>
    .navbar {
        width: 100%;
        height: 75px;
        display: flex;

        justify-content: center;
        position: fixed;

        background-color: rgb(0, 222, 0);
    }

    .navbar-sections {
        width: 33%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: inherit;
    }

    .navbar-links {
        gap: 25px;
    }

    .navbar-links-entry {
        padding: 5px 7.5px 5px 7.5px;
        text-decoration: none;
    }

    img {
        width: 50px;
        height: 50px;
    }
</style>
