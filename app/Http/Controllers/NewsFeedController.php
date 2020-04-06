<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NewsFeed;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NewsFeedController extends Controller
{
    /**
     * Create a new NewsFeedController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['show']]);
        //$this->middleware('auth:api');
    }

    /**
     * Returns a list of news stories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $newsFeed = NewsFeed::where('id', 1)->first();

        if(!$newsFeed) {
            return response()->json(['message' => 'No news feeds available.'], 404);
        }

        $rssFeed = \Feeds::make($newsFeed->url, 5, false);
        //$rssFeedData = $feed->get_items();

        $rssFeedData = array(
            'title' => $rssFeed->get_title(),
            'permalink' => $rssFeed->get_permalink(),
            'items' => $rssFeed->get_items()
        );

        for($i = 0; $i <= 4; $i++)
        {
            $response["news"][$i]["name"] = $rssFeedData["items"][$i]->get_title();
            $response["news"][$i]["link"] = $rssFeedData["items"][$i]->get_permalink();
            $response["news"][$i]["date"] = $rssFeedData["items"][$i]->get_gmdate();
        }

        return response()->json($response);
    }

    /**
     * Creates a News Feed by taking a name and a RSS feed.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $newsFeed = NewsFeed::create([
            'name' => $request->get('name'),
            'url' => $request->get('url')
        ]);

        return response()->json(compact('name','url'),201);
    }
}
