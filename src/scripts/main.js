var firstChoice = '0';
var secondChoice = '0';
var talents = {};
var callbackList = [];
var pushedIds = {};
var $matchedEl = {};
var $audio;

function shuffle(sourceArray) {
  for (var n = 0; n < sourceArray.length - 1; n++) {
    var k = n + Math.floor(Math.random() * (sourceArray.length - n));
    var temp = sourceArray[k];
    sourceArray[k] = sourceArray[n];
    sourceArray[n] = temp;
  }
  return sourceArray;
}

function updateSidebar() {
  var i = callbackList.length - 1;
  var template = "<div class=\"clearfix saved-talent\"><div class=\"col col-2\"><img src=\""+callbackList[i].original_image+"\" alt=\"\"></div><div class=\"col col-9\"><div class=\"px2\"><h3 class=\"mt0\">"+callbackList[i].name+"</h3><a href=\""+callbackList[i].url+"\"><i class=\"fa fa-thumbs-o-up\"></i> Hire</a><a href=\""+callbackList[i].url+"\"><i class=\"ml1 fa fa-dollar\"></i> Request Quote</a></div></div></div>";
  $('#sidebar').append(template);
  $('#nothing').remove();
}

function resetBoard() {
  firstChoice = '0';
  secondChoice = '0';
  $('.talents').addClass('disabled');
}

function addToList() {
  var id = $(this).siblings('img').attr('data-id');
  console.log(id);
  for (var i = talents.length - 1; i >= 0; i--) {
    if (talents[i].id == id && typeof(pushedIds[id]) == 'undefined') {
      callbackList.push(talents[i]);
      pushedIds[id] = true;
      updateSidebar();
    }
  }
}

function stopAudio() {
  $audio[0].pause();
  $audio.removeAttr('src');
}

function handleWin() {
  $matchedEl = $('[data-id='+secondChoice+']');
  $matchedEl.parent().addClass('match');
  $matchedEl.parent().parent().off('click');
  $matchedEl.parent().find('.fa-bookmark').on('click', addToList);
  // alert('They Match!');
  stopAudio();
  resetBoard();
}

function handleLoss() {
  // alert('No Match!');
  var $nomatch = $('[data-id='+secondChoice+']').last().parent();
  $nomatch.addClass('no-match');
  setTimeout(function(argument) {
    $nomatch.removeClass('no-match');
  }, 1200);
}

function checkChoice() {
  if (firstChoice === secondChoice) {
    handleWin();
  } else {
    handleLoss();
  }
}

function templateData(data, callback) {
  // scramble the data
  data = shuffle(data);
  for (var i = data.length - 1; i >= 0; i--) {
    $('#t'+i).attr({
      'data-id': data[i].id,
      'src': data[i].image,
      'alt': data[i].name
    });
  }
  data = shuffle(data);
  for (var i2 = data.length - 1; i2 >= 0; i2--) {
    $('#v'+i2).attr({
      'data-id': data[i2].id,
      'data-audio': data[i2].audio,
      'alt': data[i2].name
    });
  }
  callback();
}

function init() {
  console.log('game start');
  var $voices = $('.voices');
  var $talents = $('.talents');
  $audio = $('audio');
  $voices.on('click', '.col', function() {
    var $target = $(this).find('img');
    firstChoice = $target.attr('data-id');
    console.log($target.attr('data-id'));
    $audio.attr('src', $target.attr('data-audio'));
    $audio[0].play();
    $talents.removeClass('disabled');
  });
  $talents.on('click', '.col', function() {
    var $target = $(this).find('img');
    secondChoice = $target.attr('data-id');
    console.log($target);
    checkChoice();
  });
}

jQuery(function() {
  $.get('cache.json').then(function(res) {
    talents = res;
    // console.dir(talents);
    templateData(talents, function() {
      $(document.body).removeClass('loading');
      init();
    });
  }, function(err) {
    console.log(err);
  });
  $('img').on('error', function() {
    this.src = "http://placehold.it/180x180";
  });
});