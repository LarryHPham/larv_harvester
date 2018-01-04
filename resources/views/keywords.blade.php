@extends('layout')

@section('content')
    <div class="row">
        @if(sizeof($articles) > 0)
        <!-- Articles -->
        <div class="col-xs-12">
            <h4 class="text-center">Articles ({{ sizeof($articles) }})</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Weight</th>
                        <th>Article</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td>{{ $article[1] }}</td>
                        <td>{{ $article[0] }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ $article[2] }}">Keywords</a>
                        </td>
                        <td>
                            <a class="btn btn-primary" target="_blank" href="{{ $article[0] }}">KBB Link</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(sizeof($keywords) > 0)
        <!-- Keywords -->
        <div class="col-md-6">
            <h4 class="text-center">Keywords ({{ sizeof($keywords) }})</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Weight</th>
                        <th>Keyword</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keywords as $keyword)
                    <tr>
                        <td>{{ $keyword[1] }}</td>
                        <td>{{ $keyword[0] }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ $keyword[2] }}">Articles</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(sizeof($modified_keywords) > 0)
        <!-- Compound Keywords -->
        <div class="col-md-6">
            <h4 class="text-center">Compound Keywords ({{ sizeof($modified_keywords) }})</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Weight</th>
                        <th>Keyword</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modified_keywords as $keyword)
                    <tr>
                        <td>{{ $keyword[1] }}</td>
                        <td>{{ $keyword[0] }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ $keyword[2] }}">Articles</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endsection
