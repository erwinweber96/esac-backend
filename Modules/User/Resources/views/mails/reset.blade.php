<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=K2D%7CCabin" media="all">
<div class="container">
    <div class="card">
        <div class="logo-center">
            <img src="https://esac.gg/_nuxt/img/baea446.svg">
        </div>
        <h1>Password Reset</h1>
        <p>Seems like you forgot your password for esac.gg.
            If this is true, click below to reset your password.</p>
        <a href="https://esac.gg/reset/{{$resetToken->token}}" class="button">Reset My Password</a>
        <p>If you did not forgot your password, you can safely ignore this email.</p>
    </div>
</div>
<style>
    .logo-center {
        width: 200px;
        text-align: center;
        margin-right: auto;
        margin-left: auto;
    }
    .container {
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        color: white;
        background-color:#2a3042;
        font-family: "Cabin";
    }
    .card {
        padding-right: 18px;
        padding-left: 18px;
        padding-top: 26px;
        padding-bottom: 26px;
        width: 100%;
    }
    h1 {
        text-align: center;
        font-family: "K2D";
    }
    .button {
        color: #fff;
        background-color: #556ee6;
        padding: .3rem .7rem;
        line-height: 1.5;
        border-radius: .2rem;
        display:inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #556ee6;
        margin-right: auto;
        margin-left: auto;
        text-decoration: none;
    }
</style>
