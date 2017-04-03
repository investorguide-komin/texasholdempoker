
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12" style="padding:50px">
      <h4>Login</h4>
      <form action="index.php" method="post">
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
              <input type="hidden" name="login" value="1"/>
              <input type="submit" value="Log In"/>
            </td>
          </tr>
        </table>
      </form>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12" style="padding:50px">
      <h4>Create Account</h4>
      <form action="index.php" method="post">
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
              <input type="hidden" name="register" value="1"/>
              <input type="submit" value="Register"/>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <hr/>
              <ul>
                <? if(isset($this->message)){
                    echo "<li class='message'>".$this->message."</li>";
                } ?>
                <li>Username must be 5-15 characters with alphabets, numbers and underscores only</li>
                <li>Password must be 8-20 characters and contain at least 1 alphabet, number and symbol</li>
              </ul>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
