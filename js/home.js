
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
        url: "ajax/game.php?type=load",
        type: "post",
        async: true,
        cache: false,
        success: function(data){
          var html = "";
          var data  = $.parseJSON(data);
          if(data.code == 200 && data.games.length > 0){
            for(var i=0; i<data.games.length; i++){
              var game = data.games[i];
              var join_html = "No spots available";

              if(game.available_spots > 0){
                join_html = "<a href='game.php?id="+game.id+"' class='btn red-btn'>"+
                              "Join"+
                            "</a>";
              }

              html+="<tr id='"+i+"'>"+
                      "<td>"+game.name+"</td>"+
                      "<td>"+game.creator+"</td>"+
                      "<td>"+game.available_spots+"</td>"+
                      "<td>"+
                        join_html+
                      "</td>"+
                    "</tr>";
            }
            if(html != ""){
              $("#tabular-tbody").html(html);
            }
          }
        }
      });
    }

    load_games();
  });
