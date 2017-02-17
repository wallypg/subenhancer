// var begin = + new Date();
// var caption = '';
// var start = '';
// var counter = 1;
// window.setInterval(function(){
//       var newCaption = $('#captions p:visible');
//       if(start == '') {
//         if(newCaption.text() != '') {
//           start = + new Date();
//           caption = newCaption;
//         }
//       } else {
//         if(newCaption.text() == '' || newCaption.text() != caption.text()) {
//           var end = + new Date();
//           console.log( "\n" + counter + "\n" + secondsToHms(start-begin) + ' --> ' + secondsToHms(end-begin) );
//           counter++;
//           caption.each(function(){ console.log( this.innerText ) });
//           start = '';
//           caption = newCaption;
//         }
//       }
// }, 100);


// function secondsToHms(milliseconds) {
//     ms = milliseconds.toString().slice(-3);
//     seconds = milliseconds.toString().slice(0,-3);
//     return new Date(seconds * 1000).toISOString().substr(11, 8) + ',' + ms;
// }

// var begin = + new Date();
// var caption = '';
// var start = '';
// var counter = 1;
// window.setInterval(function(){
//       var newCaption = $('#captions p:visible');
//       if(start == '') {
//         if(newCaption.text() != '') {
//           start = + new Date();
//           caption = newCaption;
//         }
//       } else {
//         if(newCaption.text() == '' || newCaption.text() != caption.text()) {
//           var end = + new Date();
//           console.log( "\n" + counter + "\n" + secondsToHms(start-begin) + ' --> ' + secondsToHms(end-begin) );
//           counter++;
//           caption.each(function(){
//             console.log( ($($(this).html()).css('font-style') == 'italic') ? '<i>' + $(this).text().trim() + '</i>' : $(this).text().trim() );
//           });
//           start = '';
//           caption = newCaption;
//         }
//       }
// }, 100);


// function secondsToHms(milliseconds) {
//     ms = milliseconds.toString().slice(-3);
//     seconds = milliseconds.toString().slice(0,-3);
//     return new Date(seconds * 1000).toISOString().substr(11, 8) + ',' + ms;
// }

var begin = + new Date();
var caption = '';
var start = '';
var counter = 1;
window.setInterval(function(){
      var newCaption = $('#captions p:visible');
      if(start == '') {
        if(newCaption.text() != '') {
          start = + new Date();
          caption = newCaption;
        }
      } else {
        if(newCaption.text() == '' || newCaption.text() != caption.text()) {
          var end = + new Date();
          var printScreen = "\n" + counter + "\n" + secondsToHms(start-begin) + ' --> ' + secondsToHms(end-begin);
          counter++;
          caption.each(function(){
            printScreen += ($($(this).html()).css('font-style') == 'italic') ? "\n<i>" + $(this).text().trim() + "</i>" : "\n" + $(this).text().trim();
          });
          console.log(printScreen + "\n\n");
          start = '';
          caption = newCaption;
        }
      }
}, 100);


function secondsToHms(milliseconds) {
    ms = milliseconds.toString().slice(-3);
    seconds = milliseconds.toString().slice(0,-3);
    return new Date(seconds * 1000).toISOString().substr(11, 8) + ',' + ms;
}