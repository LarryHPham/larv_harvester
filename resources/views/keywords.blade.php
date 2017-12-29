@extends('layout')

@section('content')
    <div class="row">
        @foreach($data as $table)
            @if(sizeof($data) > 1)
                <div class="col-md-6">
            @endif
            <h4 class="text-center">{{ $table['title'] }}</h4>
            <table class="table table-striped{{ sizeof($data) > 1 ? " col-md-6" : " " . sizeof($data) }}">
                <thead>
                    <tr>
                        <th>Weight</th>
                        <th>Item</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table['data'] as $row)
                    <tr>
                        <td>{{ $row[1] }}</td>
                        <td>
                            @if(isset($row[3]))
                                <a href="{{ $row[0] }}">{{ $row[0] }}</a>
                            @else
                                {{ $row[0] }}
                            @endif
                        </td>
                        <td><a href="{{ $row[2] }}" class="btn btn-primary">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(sizeof($data) > 1)
                </div>
            @endif
        @endforeach
    </div>
@endsection
