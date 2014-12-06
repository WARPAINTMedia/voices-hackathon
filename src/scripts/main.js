var firstChoice = '0';
var secondChoice = '0';

function checkChoice() {
  return new Promise(function(resolve, reject) {
    if (firstChoice === secondChoice) {
      resolve(true);
    } else {
      reject(false);
    }
  });
}

jQuery(function() {
  var $voices = $('.voices');
  var $talents = $('.talents');
  $voices.on('click', '.col', function() {
    firstChoice = $(this).attr('data-id');
    console.log($(this).attr('data-id'));
    $talents.removeClass('disabled');
  });
  $talents.on('click', '.col', function() {
    secondChoice = $(this).attr('data-id');
    console.log($(this).attr('data-id'));
  });
});