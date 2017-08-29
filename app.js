/* app.js */
"use strict";


var app = {};

//под каким юзером мы "залогинены"
app.user_id = 0;

// главная приложения - отображение за кого можно залогинится
app.viewlogin = function(){
  app.api('accaunts.get',[],function(result){

    $('.app-content > div').hide();
    $('.app-content .accaunts-stage').show();
    $('.app-content .accaunts-list').html(''); //clear
    for (var i = 0; i < result['list'].length; i++){
      var obj = result['list'][i];
      var txt = '';
      txt += '<img src="avatars/'+obj.user_id+'.jpg">'+obj.name+'<span class="balance">'+obj.balance+'</span>';
      $('.app-content .accaunts-list').append('<div onclick="app.login('+obj.user_id+',\''+obj.name+'\','+obj.balance+')">'+txt+'</div>');
    }
    //
  });
}

// функция логина и отбражения главной задач
app.login = function(user_id, name, balance){
  $('.wrapper .header-bar .logout').show();
  $('.app-content > div').hide();
  $('.app-content .tasks-stage').show();

  app.user_id = user_id;
  $('.tasks-stage .user-bar .name').text(name);
  $('.tasks-stage .user-bar .balance').text(balance);

  app.taskSelectTab('my');
}

// разлогин
app.logout = function(){
  //$('.wrapper .header-bar .logout').hide();
  //app.viewlogin();
  location.reload();
}

// выбор таска с задачами
app.taskSelectTab = function(tabname){
  $('.tasks-stage .tabs > div').removeClass('selected');
  $('.tasks-stage .tabs .'+tabname).addClass('selected');

  $('.tasks-stage .task-content > div').hide();
  if(tabname =='create'){
    $('.tasks-stage .task-content .task-add').show();
    $('.task-add input[name=task-name]').val('');
    $('.task-add input[name=task-price]').val('');
    $('.tasks-stage .task-content .task-add .error-box').html('');
    $('.tasks-stage .task-content .task-add .complete-box').html('');
    $('.tasks-stage .task-content .task-add button').show();
  }
  if(tabname == 'my' || tabname == 'available'){
    $('.tasks-stage .task-content .task-list').show().html('');
    var params = {};
    params.user_id = app.user_id;
    params.type = tabname;
    app.api('tasks.get', params, function(result){
      $('.tasks-stage .task-content .task-list').html('');// повторная очистка на всякий
      for (var i = 0; i < result['list'].length; i++){
        var obj = result['list'][i];
        var txt = '';
        txt += '<span class="price">'+obj.price+'</span>';
        txt += '<span class="owner"><img src="avatars/'+obj.owner_id+'.jpg">'+obj.owner_name+'</span><br/>';
        txt += '<span class="date">'+obj.ru_date+'</span>';
        txt += '<br/><span class="name">'+obj.name+'</span>';

        var readonly = (app.user_id == obj.owner_id) ? 'true' : 'false';

        $('.tasks-stage .task-content .task-list').append('<div onclick="app.taskOpen(this,'+obj.task_id+','+readonly+');">'+txt+'</div>');
      }
      if(result['list'].length==0){
        $('.tasks-stage .task-content .task-list').append('<div class="empty-list">Список пуст</div>');
      }
    });
  }

}

app.taskOpen = function(self, task_id, readonly){
  $('.tasks-stage .tabs > div').removeClass('selected');
  $('.tasks-stage .task-content > div').hide();

  $('.tasks-stage .task-content .task-view').show();
  $('.tasks-stage .task-content .task-view .info').html($(self).html());
  $('.tasks-stage .task-content .task-view .error-box').html('');
  $('.tasks-stage .task-content .task-view .complete-box').html('');

  if(readonly == true){
    $('.tasks-stage .task-content .task-view button').hide();
  }else{
    $('.tasks-stage .task-content .task-view button').show();
    $('.tasks-stage .task-content .task-view button').attr('data-task-id',task_id);
  }
}

app.taskExecute = function(self){
  var task_id = $(self).attr('data-task-id');
  var params = {};
  params.task_id = task_id;
  params.user_id = app.user_id;

  app.api('tasks.execute', params, function(result){
    if(result.response == 1){
      $('.tasks-stage .task-content .task-view .complete-box').html('Задача успешно выполнена');
      $('.tasks-stage .task-content .task-view button').hide();
      app.updateBalance();
    }else{
      // не успешно
      result.error = (result.error) ? result.error : 'При выполнении возникла ошибка. Попробуйте позже';
      $('.tasks-stage .task-content .task-view .error-box').html(result.error);
    }
  });
}

app.updateBalance = function(){
  var params = {};
  params.user_id = app.user_id;
  app.api('users.getBalance',params,function(result){
    if(result.balance){
      $('.tasks-stage .user-bar .balance').text(result.balance);
    }
  });
}

app.transactionsHistory = function(){
  $('.tasks-stage .tabs > div').removeClass('selected');
  $('.tasks-stage .task-content > div').hide();
  $('.tasks-stage .task-content .transactions-history').show().html('');

  var params = {};
  params.user_id = app.user_id;
  app.api('transactions.history',params,function(result){
    $('.tasks-stage .task-content .transactions-history').html('');// повторная очистка на всякий
    for (var i = 0; i < result['list'].length; i++){
      var obj = result['list'][i];
      var txt = '';
      txt += '<div class="line">';
      txt += '<div class="price '+obj['color']+'">'+obj['price']+'</div>';
      txt += '<div class="user"><div class="img" style="background-image: url(avatars/'+obj['user_id']+'.jpg);"></div>'+obj['user_name']+'</div>';
      txt += '</div>';
      txt += '<div class="task-name">'+obj['task_name']+'</div>';
      txt += '<div class="date">'+obj['date']+', '+obj['transaction_status_ru']+'</div>';

      $('.tasks-stage .task-content .transactions-history').append('<div class="item">'+txt+'</div>');
    }
    if(result['list'].length==0){
      $('.tasks-stage .task-content .transactions-history').append('<div class="empty-list">Список транзакций пуст</div>');
    }
  });
}

app.taskCreate = function(){
  var params = {};
  params.user_id = app.user_id;
  params.name = $('.task-add input[name=task-name]').val();
  params.price = $('.task-add input[name=task-price]').val();

  var error_box = $('.tasks-stage .task-content .task-add .error-box');
  var errors = [];
  if(params.name=='' && params.price == ''){
    errors.push('Необходимо заполнить все поля');
  }else{
    if(params.name==''){
      errors.push('Необходимо заполнить название');
    }else if(params.name.trim().length<5){
      errors.push('Название должно быть более 5 символов');
    }
    if(params.price == ''){
      errors.push('Необходимо заполнить стоимость');
    }else if(parseInt(params.price) < 10){
      errors.push('Стоимость должна быть больше 10');
    }
  }

  if(errors.length>0){
    errors = errors.join('<br/>');
    error_box.html(errors);
  }else{
    //save
    error_box.html('');
    app.api('tasks.create',params,function(result){
      if(result.response == 1){
        $('.tasks-stage .task-content .task-add .complete-box').html('Задача успешно добавлена');
        $('.tasks-stage .task-content .task-add button').hide();
      }else{
        // не успешно
        result.error = (result.error) ? result.error : 'При добавлении возникла ошибка. Попробуйте позже';
        error_box.html(result.error);
      }
    });
  }
}




app.api = function(method,params,callback){
	$.ajax({
	  url: '/sample-transaction/app/?method='+method,
	  data: params,
	  type: "POST",
	  dataType: 'json',
      cache: true,
	  success: function(result){

		  //if(callback){
			  if(result){
					  callback(result);
			  }
		  //}
	  }
	});
}





$(document).ready(function(){

  app.viewlogin();


});
