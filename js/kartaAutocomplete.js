document.addEventListener('DOMContentLoaded', function() {
  var input = document.getElementById("nazevAutoComplete");
  if(input) {
    input.focus();
  }
});

function getAutocompleteConfig(id) {
  var isIdNazev = (id == "nazev");
  var elSelect = document.getElementById(id);
  var src = getAllOptionsInSelect(elSelect);

  var config = {
      selector: "#" + id + "AutoComplete",
      placeHolder: "Hledat " + elSelect.labels[0].innerHTML,
      data: {
          src: src
      },
      resultsList: {
          maxResults: 10
      },
      resultItem: {
          highlight: {
              render: true
          }
      },
      events: {
          input: {
              selection: function(event) {
                  const selection = event.detail.selection.value;
                  elSelect.value = selection;
                  if(isIdNazev) {
                    autoCompleteForNazev.input.value = selection;
                    vyber_cv();                    
                  }
                  else {
                    autoCompleteForCv.input.value = selection;
                    osetri_cv();   
                  }
              }
          }
      }
  };

  return config;
}


function getAllOptionsInSelect(elSelect) {
  var options = new Array();

  for (var i = 0; i < (elSelect.options).length; i++) {
    var val = elSelect.options[i].value;
    if(val) {
      options.push(val);
    }
  }

  return options;
}


