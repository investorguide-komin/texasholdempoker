
  $(function(){
    $("body").on("submit", "#create-game-form", function(){
      var form  = $(this);
      var game_name = form.find("input[name='name']").val().trim();

      $.ajax({
        url: "ajax/game.php?type=create",
        type: "post",
        async: true,
        cache: false,
        data: {name: game_name},
        success: function(data){
          var data  = $.parseJSON(data);
          if(data.code == 200){
            load_games();
          }
        }
      });
    });

    function load_games(){
      $.ajax({
        url: "ajax/game.php?type=create",
        type: "post",
        async: true,
        cache: false,
        data: {name: game_name},
        success: function(data){
          console.log(data);
          var data  = $.parseJSON(data);
          console.log(data);
        }
      });
    }

    if($("#tabular").length){
      load_games();
    }
  });
