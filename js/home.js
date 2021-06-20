
function automatic_update_status(){
    //current_search_line
    //current_search_array
    var actual_current_line = current_search_line+1;
    if (actual_current_line>current_search_array.length){
        console.log('Search List Complete...');
        update_status('Search List Complete...');
        $('#now_searching').html('Search Complete. Download Files.');

        $('#download_file').css('display','inline-block');
    }else{
        $('#download_file').css('display','none');

    var search = '';

    if(current_search_array[0].length>=2){
        search = $.trim(current_search_array[current_search_line][0])+' '+$.trim(current_search_array[current_search_line][1]);
    }else{
        search = current_search_array[current_search_line][0];
    }
    
        //console.log(search);
        // Delay search to look more like human
        timeout = setTimeout(get_search_results(search), 500);
            
        }
}

//New Search Address Submit
function update_status(text){

    setTimeout(function(){
        $('#status').css("opacity",0);
        $('#status').html(text);
        $('#status').css("opacity",1);
    }, 2000);
   
    
}


function get_search_results(search){
    $('#search').val(search);

    var l = current_search_array.length;
    var i = current_search_line+1;

    var search_match = search.match(/^(\w+|\s+)\s+\w+/);
    if(search_match!=null && search_match.length>0){
        $('#now_searching').html(i+'/'+l+' Searching');

        $('#search_loader').css('display','inline-block');
        update_status('Searching google.com for top 50 results');
        
        $.ajax({  
            url:"search.php",  
            method:"POST",  
            data: {
                    search:search
                },  
                success:function(data){  
                    $('#table_body').html(data);
                    run_page_search();
                }  
        });
    }else{
         // Run next search term
         current_search_line++;
        automatic_update_status(); 
    }
    
}

//Run scrapper for loaded pages
var table_rows = [];

function run_page_search(){

    table_rows = [];
    
    $('#search_loader').css('display','none');
    update_status('Formatting Tables...');

    var search = $('#search').val();
    table_rows.push({search:search});
    var values = [];

    $('#table_body tr').each(function(i,item){
        var weight = parseInt($(this).children('#t_weight').html());
        var link = $(this).children('#t_link').html();
        var title = $(this).children('#t_title').html();
        var domain = $(this).children('#t_domain').html();

        var x = {
                title:title,
                link:link,
                weight:weight,
                domain:domain,
                scrapped:0,
                conf:0
                }

        values.push(x);
    });
    table_rows.push({values:values})
    select_search_urls(table_rows);
}

//Select high confident search urls
function select_search_urls(){
    var search = table_rows[0].search;

    
    //get house number & street name from search
    var search_split = search.split(' ').slice(0,2); 
    if (typeof search_split[1] === "undefined"){
        search_split[1] = '';
    }
    var house_number = search_split[0].toLowerCase();
    var house_street = search_split[1].toLowerCase();

    $.each(table_rows[1].values,function(i,item){
        var title = item.title.toLowerCase();
        console.log(title+" -title matching on row"+i);
        var house_number_contains = title.indexOf(house_number);
        var house_street_contains = title.indexOf(house_street);
        var str = house_number+' '+house_street;
        var house_number_and_street_contains = title.toLowerCase().indexOf(str);

        console.log('house_number:'+house_number+house_number_contains);
        console.log('house_street:'+house_street+house_street_contains);
        console.log('house_number_&_street:'+str+house_number_and_street_contains);

        //Title contains street number and / OR address seperately or together
        if (house_number_contains  >= 0 || house_street_contains >=0){
            table_rows[1].values[i].conf=5;
        }
        //Title contains street number and address seperately or together
        if (house_number_contains >= 0 && house_street_contains >=0){
            table_rows[1].values[i].conf=10;
        }
        //Title contains street number and address together
        if (house_number_and_street_contains >= 0){
            table_rows[1].values[i].conf=20;
        }

        // Comparing confidence with domain weight
        var weight = item.weight;
        var conf = parseInt(table_rows[1].values[i].conf);

        console.log(weight+' '+conf);

        if (item.weight==100 && conf==20){
            table_rows[1].values[i].confidence=100; // Very High trustworthy site for data

        }else if(item.weight==100 && conf==10){
            table_rows[1].values[i].confidence=90; // High trustworthy site for data

        }else if (item.weight==100 && conf==5){
            table_rows[1].values[i].confidence=80 // Trustsed domain has street number or street name

        }else if (item.weight>=80 && conf>=10){
            table_rows[1].values[i].confidence=80; // Sites known to have given results

        }else if (item.weight>=80 && conf>=5){
            table_rows[1].values[i].confidence=70; // Sites known to have given results

        }else if (conf>=10){
            table_rows[1].values[i].confidence=70; // Sites known to have given results

        }else if (conf>=5){
            table_rows[1].values[i].confidence=60; // Sites known to have given results

        }else{
            table_rows[1].values[i].confidence=0; // No confidence. Skip scrapping.
        }
    });

    //Sorting values array by confidence
    table_rows[1].values.sort(function(a, b) {
        return b[5] - a[5];
      });
      
      //console.log(table_rows);

    start_scraping();
}

//Start Scraping
function start_scraping(){

    $.each(table_rows[1].values,function(i,item){
       var link = item.link;
       var domain = item.domain;
       var confidence = parseInt(item.confidence);
       var scrapped = parseInt(item.scrapped);
       var no_of_rows = table_rows[1].values.length;
       var last_row_index = no_of_rows-1;

       //First preference for confidence 100 sites to fill fields
       if(scrapped!=0){
            return true;
       }else if(confidence==100 ){
            ajax_search_pages(link,domain,i,confidence);
            update_status('Scraping 100% confident link: '+domain);
            return false;

       }else if(confidence==90){
            ajax_search_pages(link,domain,i,confidence);
            update_status('Scraping 90% confident link: '+domain);
            return false;

       }else if(confidence==80){
            ajax_search_pages(link,domain,i,confidence);
            update_status('Scraping 80% confident link: '+domain);
            return false;

        }else if(confidence==70){
            ajax_search_pages(link,domain,i,confidence);
            update_status('Scraping 70% confident link: '+domain); 
            return false;

        }else if(confidence==60){
            ajax_search_pages(link,domain,i,confidence);
            update_status('Scraping 60% confident link: '+domain); 
            return false;
        }else{
            table_rows[1].values[i].scrapped = 2; // rejected links
            console.log(table_rows[1].values[i].domain+" skipped.")
                
                if(i==last_row_index){ // Last row of search has also been processed
                    search_finished();
                }
                
        }
    });
}


//--------------- Search term finished
function search_finished(){
    console.log("Scraping finished for line: "+current_search_line+" of "+current_search_array.length);
    console.log(table_rows); 
    update_status("Scraping finished for line: "+current_search_line+" of "+current_search_array.length);
    analyze_and_fill_data_row();
 
}
function analyze_and_fill_data_row(){

    var x = '<tr>';
    $('#populating_data_fields .col-md-4 ul').each(function(i,item){
        var td = $(this).children('li:first').html();
        if (typeof td == "undefined" || td == null){td = '';}
        x = x +'<td>'+td+'</td>';
    });
    x = x + '</tr>'; 

    $('#records_table_body').append(x);

    //Clear HTML data
    $('#table_body').html('');
    $('#populating_data_fields .col-md-4 ul').each(function(){
        $(this).html('');
    });

    // Run next search term
    current_search_line++;
    automatic_update_status(); 
}




//Call to ajax search pages
function ajax_search_pages(link,domain,row,confidence){

    $.ajax({  
        url:"search_pages.php",  
        method:"GET",  
        data: {
                link:link,
                domain:domain,
                confidence:confidence
            },  
        success:function(data){ 
                table_rows[1].values[row].scrapped = 1;
                data = JSON.parse(data);
                console.log(data);
                
                $.each(data, function(i,item){
                    if(item=='' || item==null || item==' '){

                    }else{
                        var element = $('#'+i).append("<li datax-row='"+i+"' datax-conf='"+confidence+"' class='conf_"+confidence+"'>"+item+"</li>");
                    }
                    
                });
                start_scraping();
               
                
        },  
        error:function(data){
                console.log("error:");
                console.log(data);

                table_rows[1].values[row].scrapped = 2;
                start_scraping();
        }
    });
    
    console.log(table_rows);
}

$(function() {
    $('#toggle_show_progress').change(function() {
      var i = $(this).prop('checked');
      if(i){
        //console.log('show');
        $('#show_progress_container').show();
    }else{
       // console.log('hide');
        $('#show_progress_container').hide();
    }
    });
});

  $(function() {

    $('#toggle_show_fields').change(function() {
        var i = $(this).prop('checked');
        if(i){
            console.log('show');
          $('#fields_container').css('display','block');
      }else{
        console.log('hide');
          $('#fields_container').css('display','none');
      }
      })
  });

  