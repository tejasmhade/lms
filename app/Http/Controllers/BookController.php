<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BooksRented;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $books = Book::all();
        $response['status'] = 'success';
        $response['data'] = ['books' => $books];
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'book_name' => 'required|string|max:255|unique:books',
            'author' => 'required|string|max:255',
            'cover_image' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        DB::beginTransaction();
        $book = Book::create([
            'book_name' => $request->get('book_name'),
            'author' => $request->get('author'),
            'cover_image' => $request->get('cover_image'),
            'status' => 1,
        ]);
        DB::commit();

        $response['status'] = 'success';
        $response['data'] = ['book' => $book];
        $response['message'] = "Book added successfully.";
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $book = Book::find($id);
        if (!empty($book)) {
            $response['status'] = 'success';
            $response['data'] = ['book' => $book];
        } else {
            $response['status'] = 'error';
            $response['message'] = "Book not found.";
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'book_name' => 'required|string|max:255|unique:books,book_name,' . $id . ',b_id',
            'author' => 'required|string|max:255',
            'cover_image' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        DB::beginTransaction();
        $book = Book::find($id);
        if (empty($book)) {
            $response['status'] = 'error';
            $response['message'] = "Book not found.";
            return response()->json($response, 404);
        }

        $book->fill($data);
        $book->save();
        DB::commit();

        $response['status'] = 'success';
        $response['data'] = ['book' => $book];
        $response['message'] = "Book updated successfully.";
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        $book = Book::find($id);
        if (empty($book)) {
            $response['status'] = 'error';
            $response['message'] = "Book not found.";
            return response()->json($response, 404);
        }

        $book->delete();
        DB::commit();

        $response['status'] = 'success';
        $response['data'] = ['book' => $book];
        $response['message'] = "Book deleted successfully.";
        return response()->json($response, 200);
    }

    public function getRentedBooks(Request $request, $u_id)
    {
        $data = BooksRented::select('u.u_id', 'u.firstname', 'u.lastname', 'u.email', 'b.b_id', 'b.book_name', 'b.author', 'br.issued_on', 'br.returned_on')
            ->from('books_rented as br')
            ->join('books as b', 'b.b_id', 'br.b_id')
            ->join('users as u', 'u.u_id', 'br.u_id')
            ->where('br.u_id', $u_id)
            ->get();

        $response['status'] = 'success';
        $response['data'] = $data;
        return response()->json($response, 200);
    }

    public function issueBook(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'u_id' => 'required|numeric|exists:users,u_id',
            'b_id' => 'required|numeric|exists:books,b_id',
            'issued_on' => 'required|date_format:Y-m-d H:i:s|before_or_equal:' . date('Y-m-d H:i:s'),
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        $already_issued = BooksRented::where('u_id', $request->get('u_id'))
            ->where('b_id', $request->get('b_id'))
            ->whereNull('returned_on')
            ->first();

        if (!empty($already_issued)) {
            $response['status'] = 'error';
            $response['message'] = "The same book is already issued to this user.";
            return response()->json($response, 400);
        }

        $book = BooksRented::create([
            'u_id' => $request->get('u_id'),
            'b_id' => $request->get('b_id'),
            'issued_on' => $request->get('issued_on'),
        ]);

        $response['status'] = 'success';
        $response['data'] = $book;
        $response['message'] = "Book issued successfully.";
        return response()->json($response, 200);
    }

    public function returnBook(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'returned_on' => 'required|date_format:Y-m-d H:i:s|before_or_equal:' . date('Y-m-d H:i:s'),
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        $book = BooksRented::whereNull('returned_on')
            ->find($id);

        if (empty($book)) {
            $response['status'] = 'error';
            $response['message'] = "Data not found.";
            return response()->json($response, 400);
        }

        if ($request->get('returned_on') < $book->issued_on) {
            $response['status'] = 'error';
            $response['message'] = "Returned date should be greater than the issued date.";
            return response()->json($response, 400);
        }

        $book->returned_on = $request->get('returned_on');
        $book->save();

        $response['status'] = 'success';
        $response['data'] = $book;
        $response['message'] = "Book returned successfully.";
        return response()->json($response, 200);
    }
}
