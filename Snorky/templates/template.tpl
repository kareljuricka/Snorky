
{: literal :}
<h4>Syntaxe snorkyho je nasledujici: </h4><br>

<dd>{: foreach $pole as [ $key => ] $row :} - pro foreach smycku</dd><br>
<dd> {: $var :} - pro vypsani promene, pouziva se klasicke php adresovani </dd><br>
<dd> {: first :} ... {: block_end :} - pro omezeni jenom na prvni pruchod </dd><br>
<dd> {: plugin=plugin_name [method=metoda] [label=nalepka] [cacheable] :} - pro vlozeni pluginu </dd><br>


{: literal_end :}

<div>
    {: foreach $pole as $row :}
    
    {: first :}<h4> first</h4> {: block_end :}
    {: last :}<h4> last</h4> {: block_end :}
    {: iteration = 3:}  <font color="green">kazda 3. iterace </font><br> {: block_end :}
    {: even :}
        <font color="red"> 
            <h3> {: iterator :}. {: $row  :} </h3>  
        </font>
    {: block_end :} 
    {: odd :}
        <font color="blue"> 
            <h3> {: $row  :} </h3>  
        </font>
    {: block_end :} 
    
    {: block_end :}
    
</div>
    
    <h2>{: $pole[5] :} </h2>