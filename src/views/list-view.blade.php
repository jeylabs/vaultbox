@if((sizeof($files) > 0) || (sizeof($directories) > 0))
    <table class="table table-condensed table-striped">
        <thead>
        <tr>
            <th style='width:50%;'>{{ Lang::get('vaultbox::vaultbox.title-item') }}</th>
            <th>{{ Lang::get('vaultbox::vaultbox.title-size') }}</th>
            <th>{{ Lang::get('vaultbox::vaultbox.title-type') }}</th>
            <th>{{ Lang::get('vaultbox::vaultbox.title-modified') }}</th>
            <th>{{ Lang::get('vaultbox::vaultbox.title-action') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($directories as $key => $directory)
            <tr>
                <td>
                    <i class="fa fa-folder-o"></i>
                    <a class="folder-item clickable" data-id="{{ $directory->path }}">
                        {{ $directory->name }}
                    </a>
                </td>
                <td></td>
                <td>{{ Lang::get('vaultbox::vaultbox.type-folder') }}</td>
                <td></td>
                <td></td>
            </tr>
        @endforeach

        @foreach($files as $file)
            <tr>
                <td>
                    <i class="fa {{ $file['icon'] }}"></i>
                    <?php $file_name = $file['name'];?>
                    <a href="javascript:useFile('{{ $file_name }}')" id="{{ $file_name }}"
                       data-url="{{ $file['url'] }}">
                        {{ $file_name }}
                    </a>
                    &nbsp;&nbsp;
                    <a href="javascript:rename('{{ $file_name }}')">
                        <i class="fa fa-edit"></i>
                    </a>
                </td>
                <td>
                    {{ $file['size'] }}
                </td>
                <td>
                    {{ $file['type'] }}
                </td>
                <td>
                    {{ date("Y-m-d h:m", $file['updated']) }}
                </td>
                <td>
                    <a href="javascript:trash('{{ $file_name }}')">
                        <i class="fa fa-trash fa-fw"></i>
                    </a>
                    @if($file['thumb'])
                        <a href="javascript:cropImage('{{ $file_name }}')">
                            <i class="fa fa-crop fa-fw"></i>
                        </a>
                        <a href="javascript:resizeImage('{{ $file_name }}')">
                            <i class="fa fa-arrows fa-fw"></i>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@else
    <div class="row">
        <div class="col-md-12">
            <p>{{ Lang::get('vaultbox::vaultbox.message-empty') }}</p>
        </div>
    </div>
@endif
