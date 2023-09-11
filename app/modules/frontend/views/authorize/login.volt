<div class="card">
    <div class="card-body">

        <h3 class="text-center mt-0 m-b-15">
            <div class="row">
                <div class="col-sm-12">
                    <a href="/" class="logo">
                        <img src="{{ config.site.logo }}" alt="{{ config.site.name }}" width="100%">
                    </a>
                </div>
            </div>
        </h3>

        <div class="p-3">

            <h4 class="text-center">Login</h4>

            {{ flash.output() }}

            <form class="form-horizontal m-t-20" action="" method="post">
                <div class="form-group row">
                    <div class="col-12">
                        <label for="name">Name</label>
                        <input class="form-control" type="text" required="" name="name" placeholder="Name" id="name">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-12">
                        <label for="username">Username</label>
                        <input class="form-control" type="text" required="" name="username" placeholder="Username" id="username">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-12">
                        <label for="password">Password</label>
                        <input class="form-control" type="password" required="" name="password" placeholder="Password" id="password">
                    </div>
                </div>

                <div class="form-group text-center row m-t-20">
                    <div class="col-12">
                        <button class="btn btn-danger btn-block waves-effect waves-light" type="submit">Log In</button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
