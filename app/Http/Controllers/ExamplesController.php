<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\Yaml\Yaml;

class ExamplesController extends Controller
{
    public function __construct(Documentation $documentation)
    {
        $this->documentation = $documentation;
    }

    public function show($view)
    {
        $yaml = Yaml::parse($this->documentation->get('examples.yml'));
        $example = json_decode(json_encode($yaml[$view]));

        return view('examples.show', compact('example'));
    }
}
