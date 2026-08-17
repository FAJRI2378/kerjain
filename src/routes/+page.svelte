<script>
import "../styles/global.css"
import placeholder_logo from "../images/furinanerd-placeholder.jpg"
import Navbar from "../components/navbar.svelte";
</script>
<Navbar></Navbar>
<div class="screen-div">
<img id="logo-kerjain-home" src={placeholder_logo} alt="furinanerd">
<p>(placeholder logo /\)</p>
<h1>
    KERJAIN
</h1>
<h2>
    Sistem terintegrasi lorem ipsum
</h2>
<h2>
    Dolor sit amet
</h2>
</div>

<style>
    #logo-kerjain-home {
        width: 150px;
        margin-bottom: 25px;
    }
</style>
