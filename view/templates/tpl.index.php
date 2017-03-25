
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding:50px">
      <h4>Login</h4>
      <form action="login.php" method="post">
        <table>
          <tr>
            <td>
              <label for="username">Username</label>
            </td>
            <td>
              <input type="text" name="username" id="username" autocomplete="off"/>
            </td>
          </tr>
          <tr>
            <td>
              <label for="password">Password</label>
            </td>
            <td>
              <input type="password" name="password" id="password"/>
            </td>
          </tr>
          <tr>
            <td></td>
            <td>
              <input type="submit" value="Log In"/>
            </td>
          </tr>
        </table>
      </form>
    </div>
    <div class="col-lg-2 col-md-2"></div>
    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding:50px">
      <h4>Create Account</h4>
      <form action="register.php" method="post">
        <table>
          <tr>
            <td>
              <label>Username</label>
            </td>
            <td>
              <input type="text" name="username" autocomplete="off"/>
            </td>
          </tr>
          <tr>
            <td>
              <label for="reg-password">Password</label>
            </td>
            <td>
              <input type="password" name="password"/>
            </td>
          </tr>
          <tr>
            <td>
              <label>Re-type Password</label>
            </td>
            <td>
              <input type="password" name="password_confirm"/>
            </td>
          </tr>
          <tr>
            <td></td>
            <td>
              <input type="submit" value="Register"/>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
