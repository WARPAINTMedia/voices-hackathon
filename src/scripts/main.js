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
  // body...
});