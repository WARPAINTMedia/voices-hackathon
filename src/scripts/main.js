var firstChoice = '0';
var secondChoice = '0';
var talents = {};

function shuffle(sourceArray) {
  for (var n = 0; n < sourceArray.length - 1; n++) {
    var k = n + Math.floor(Math.random() * (sourceArray.length - n));
    var temp = sourceArray[k];
    sourceArray[k] = sourceArray[n];
    sourceArray[n] = temp;
  }
  return sourceArray;
}

function resetBoard() {
  firstChoice = '0';
  secondChoice = '0';
  $('.talents').addClass('disabled');
}

function handleWin() {
  console.log('They Match!');
}

function handleLoss() {
  console.error('No Match!');
  resetBoard();
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
    $('#v'+i).attr({
      'data-id': data[i].id,
      'data-audio': data[i].audio,
      'alt': data[i].name
    });
  }
  callback();
}

function init() {
  var $voices = $('.voices');
  var $talents = $('.talents');
  $voices.on('click', '.col', function() {
    var $target = $(this).find('img').attr('data-id');
    firstChoice = $target;
    console.log($target);
    $talents.removeClass('disabled');
  });
  $talents.on('click', '.col', function() {
    var $target = $(this).find('img').attr('data-id');
    secondChoice = $target;
    console.log($target);
    checkChoice();
  });
}

jQuery(function() {
  $.get('cache.json').then(function(res) {
    talents = res;
    console.log(talents);
    templateData(talents, function() {
      $(document.body).removeClass('loading');
      init();
    });
  }, function(err) {
    console.log(err);
  });
});