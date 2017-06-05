function chomp(raw_text)
{
  return raw_text.replace(/(\n|\r)+$/, '');
}

function updateUser (name, shiftnum, table, begin_date, starthr, endhr) {
    var myName = document.getElementById("namebox").value;
name = chomp(name);
xmlhttp=new XMLHttpRequest();
xmlhttp.open("GET","/Circ/update_fall_name.php?q="+name+"&r="+shiftnum+"&t="+table+"&u="+begin_date+"&v="+starthr+"&w="+endhr,true);
xmlhttp.send();
}

function ReadingDates (date, col) {
    var myName = document.getElementById("dates").value;
xmlhttp=new XMLHttpRequest();
xmlhttp.open("GET","/Circ/update_readingwkdates.php?q="+date+"&r="+col,true);
xmlhttp.send();
}

function addTimeSlot (id, table) {
        updateUser("open", id, table);
        setTimeout("window.location.reload()",900);
   }
