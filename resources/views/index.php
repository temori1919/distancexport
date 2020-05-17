<!DOCTYPE html>
<html>
<head>
    <meta name=”robots” content=”noindex,noarchive,nofollow”>
    <title>distancexport</title>
    <link rel="stylesheet" href="" type="text/css" />
    <style>
        <?php echo \Temori\Distancexport\Renders\AssetResponse::assets('css/jsuites.css');?>
        <?php echo \Temori\Distancexport\Renders\AssetResponse::assets('css/jexcel.css');?>
    </style>
    <script type="application/javascript">
        <?php echo \Temori\Distancexport\Renders\AssetResponse::assets('js/jsuites.js');?>
        <?php echo \Temori\Distancexport\Renders\AssetResponse::assets('js/jexcel.js');?>
    </script>
    <style>
        .jexcel > tbody > tr > td.readonly {
            color: #6d6d72;
        }
    </style>
</head>
<body>
    <form id="form" action="./" method="post" style="margin-bottom: 1rem;">
        <button onclick="onExec(event)" type="button" value="dry">Dry run</button>
        <button onclick="onExec(event)" type="button" value="run">Run</button>
        <input type="hidden" name="params" id="params"/>
        <input type="hidden" name="runtype" id="runtype"/>
        <?php if(defined('DX_CSRF_TOKEN_NAME') && defined('DX_CSRF_TOKEN')):?>
          <input type="hidden" name="<?php echo DX_CSRF_TOKEN_NAME;?>" value="<?php echo DX_CSRF_TOKEN;?>"/>
        <?php endif;?>
    </form>
    <div id="spreadsheet" style="display: inline-block;"></div>
    <script type="application/javascript">
      var destination_column = <?php echo $destination_column;?>;
      var background = {};
      for (var i = 0; i < destination_column.length; i++) {
        background['G' + (i + 1)] = 'background-color: #ccc; ';
        background['N' + (i + 1)] = 'background-color: #fff8e5; ';
        // If exsit errors.
        if (destination_column[i][14]) {
          background['O' + (i + 1)] = 'background-color: #ff4e4e; ';
        }
      }
      var table = jexcel(document.getElementById('spreadsheet'), {
        data: destination_column,
        columnSorting: false,
        columns: [
          { type: 'text', title: 'Field', width:200, readOnly:true },
          { type: 'text', title: 'Type', readOnly:true },
          { type: 'text', title: 'Null', readOnly:true },
          { type: 'text', title: 'Key', readOnly:true },
          { type: 'text', title: 'Default', readOnly:true },
          { type: 'text', title: 'Extra', readOnly:true },
          { type: 'text', title: '　', readOnly:true },
          { type: 'text', title: 'Field', width:200 },
          { type: 'text', title: 'Type' },
          { type: 'text', title: 'Null' },
          { type: 'text', title: 'Key' },
          { type: 'text', title: 'Default' },
          { type: 'text', title: 'Extra' },
          { type: 'text', title: 'Uniformity', width: 100 },
          { type: 'text', title: 'message', width:200  }
        ],
        nestedHeaders:[
          [
            { title:'Destination Databases', colspan:'6' },
            { title:'', colspan:'1' },
            { title:'Source Databases', colspan:'7' },
            { title:'Results', colspan:'1' },
          ]
        ],
        style: background,
        onload: function () {
          var done = <?php echo isset($done) ? 'true' : 'false';?>;

          if (done) {
            alert('Done processes.');
          }
        }
      });

      function onExec(e) {
        if (window.confirm('Are you sure to run processes?')) {
          document.getElementById('params').value = JSON.stringify(table.getJson());
          document.getElementById('runtype').value = e.target.value;
          document.getElementById('form').submit();
        }
      }
    </script>
</body>
</html>
