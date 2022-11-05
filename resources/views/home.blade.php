@extends('layouts.app')


@section('HEADCONTENT')
    <title>Home | SpaceNews</title>
@endsection

@php
    use App\Functions\Fetch;
    
    try {
        if (Cache::has('cachedArticles')) {
            $articles = Cache::store('redis')->get('cachedArticles');
    
            // echo 'articles served from cache';
        } else {
            $articles = Cache::store('redis')->remember('cachedArticles', 60 * 15, function () {
                return (new Fetch())->fetch_articles();
            });
    
            // echo 'articles served from API';
        }
    
        if (Cache::has('cachedMarsPhotoData')) {
            $marsPhotoCollection = Cache::store('redis')->get('cachedMarsPhotoData');
    
            // echo 'mars photos served from cache';
        } else {
            $marsPhotoCollection = Cache::store('redis')->remember('cachedMarsPhotoData', 60 * 60 * 24, function () {
                return (new Fetch())->fetch_mars_photos();
            });
    
            // echo 'mars photos served from API';
        }
    } catch (\Throwable $th) {
        $redisAvailable = false;
        $fetch = new Fetch();
    
        $articles = $fetch->fetch_articles();
        $marsPhotoCollection = $fetch->fetch_mars_photos();
    }
@endphp

@section('MAINCONTENT')
    @if (isset($redisAvailable) && !$redisAvailable)
        <div class="absolute right-1 top-1 flex items-center z-10 p-4 space-x-4 w-full max-w-xs text-white bg-slate-400 rounded-lg divide-x divide-gray-200 shadow dark:text-gray-400 dark:divide-gray-700 space-x dark:bg-gray-800"
            id="redis-error-toast" role="alert">
            <span class="material-icons">
                warning
            </span>
            <div class="pl-4 text-sm font-normal">
                The results are served from the API, `redis-server` is not installed in the current hosting environment,
                thus, the results are not cached.
            </div>
            <button type="button"
                class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                data-button-type="close" data-dismiss-target="#redis-error-toast" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    @endif
    <div class="mt-10">

        @if (empty($articles))
            <p class="text-center text-xl text-red-500 font-light my-20">
                Couldn't fetch the results, please try again after some time.
            </p>
        @else
            @php
                $limit = 12;
                
                if (!empty($marsPhotoCollection)) {
                    $photoObject = $marsPhotoCollection[array_rand($marsPhotoCollection, 1)];
                }
            @endphp

            <div id="card-containers">

                @foreach (array_splice($articles, null, $limit) as $article)
                    @include('components.card')
                @endforeach

            </div>

            <div class="flex justify-center align-middle my-20" id="load-more">
                <button type="button" name="load" value="true" onclick="loadMoreCards()"
                    class="border rounded-md shadow-sm outline-none px-5 py-1 text-gray-600 hover:bg-sky-400 hover:text-white">Load
                    More</button>
            </div>
        @endif

    </div>
@endsection
