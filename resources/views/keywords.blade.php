@extends('layout')

@section('content')
    @if(isset($articles) && sizeof($articles) > 0)
    <div class="row">
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
    </div>
    @endif

    <div class="row">
        @if(isset($keywords) && sizeof($keywords) > 0)
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

        @if(isset($modified_keywords) && sizeof($modified_keywords) > 0)
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

    <div class="row">
        @if(isset($articles_to) && sizeof($articles_to) > 0)
        <!-- Articles -->
        <div class="col-md-6">
            <h4 class="text-center">Links To ({{ sizeof($articles_to) }})</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles_to as $article)
                    <tr>
                        <td>{{ $article[0] }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ $article[1] }}">Keywords</a>
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

        @if(isset($articles_from) && sizeof($articles_from) > 0)
        <!-- Articles -->
        <div class="col-md-6">
            <h4 class="text-center">Linked From ({{ sizeof($articles_from) }})</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles_from as $article)
                    <tr>
                        <td>{{ $article[0] }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ $article[1] }}">Keywords</a>
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
    </div>
@endsection
