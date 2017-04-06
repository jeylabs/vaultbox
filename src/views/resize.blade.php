<div class="row">
  <div class="col-md-8" id="containment">
    <img id="resize" src="{{ asset($img) }}" height="{{ $height }}" width="{{ $width }}">
  </div>
  <div class="col-md-4">

    <table class="table table-compact table-striped">
      <thead></thead>
      <tbody>
        @if ($scaled)
        <tr>
          <td>{{ trans('vaultbox::Vaultbox.resize-ratio') }}</td>
          <td>{{ number_format($ratio, 2) }}</td>
        </tr>
        <tr>
          <td>{{ trans('vaultbox::Vaultbox.resize-scaled') }}</td>
          <td>
            {{ trans('vaultbox::Vaultbox.resize-true') }}
          </td>
        </tr>
        @endif
        <tr>
          <td>{{ trans('vaultbox::Vaultbox.resize-old-height') }}</td>
          <td>{{ $original_height }}px</td>
        </tr>
        <tr>
          <td>{{ trans('vaultbox::Vaultbox.resize-old-width') }}</td>
          <td>{{ $original_width }}px</td>
        </tr>
        <tr>
          <td>{{ trans('vaultbox::Vaultbox.resize-new-height') }}</td>
          <td><span id="height_display"></span></td>
        </tr>
        <tr>
          <td>{{ trans('vaultbox::Vaultbox.resize-new-width') }}</td>
          <td><span id="width_display"></span></td>
        </tr>
      </tbody>
    </table>

    <button class="btn btn-primary" onclick="doResize()">{{ trans('vaultbox::Vaultbox.btn-resize') }}</button>
    <button class="btn btn-info" onclick="loadItems()">{{ trans('vaultbox::Vaultbox.btn-cancel') }}</button>

    <input type="hidden" name="ratio" value="{{ $ratio }}"><br>
    <input type="hidden" name="scaled" value="{{ $scaled }}"><br>
    <input type="hidden" id="original_height" name="original_height" value="{{ $original_height }}"><br>
    <input type="hidden" id="original_width" name="original_width" value="{{ $original_width }}"><br>
    <input type="hidden" id="height" name="height" value=""><br>
    <input type="hidden" id="width" name="width">

  </div>
</div>

<script>
  $(document).ready(function () {
    $("#height_display").html($("#resize").height() + "px");
    $("#width_display").html($("#resize").width() + "px");

    $("#resize").resizable({
      aspectRatio: true,
      containment: "#containment",
      handles: "n, e, s, w, se, sw, ne, nw",
      resize: function (event, ui) {
        $("#width").val($("#resize").width());
        $("#height").val($("#resize").height());
        $("#height_display").html($("#resize").height() + "px");
        $("#width_display").html($("#resize").width() + "px");
      }
    });
  });

  function doResize() {
    $.ajax({
      type: "GET",
      dataType: "text",
      url: "{{ route('jeylabs.vaultbox.performResize') }}",
      data: {
        img: '{{ parse_url($img, PHP_URL_PATH) }}',
        working_dir: $("#working_dir").val(),
        dataX: $("#dataX").val(),
        dataY: $("#dataY").val(),
        dataHeight: $("#height").val(),
        dataWidth: $("#width").val()
      },
      cache: false
    }).done(function (data) {
      if (data == "OK") {
        loadItems();
      } else {
        notify(data);
      }
    });
  }
</script>
