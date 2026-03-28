 const queen = document.getElementById('queen');
 const queen_color = document.getElementById('queen_color');
 
    if (queen) {
        queen.addEventListener('change',function(){
            if (this.checked) {
                queen_color.classList.add("show_queen_color");
            } else {
                queen_color.classList.remove("show_queen_color");
            }
        });
    }
