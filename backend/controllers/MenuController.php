// POST ?r=menu&a=delete  Body JSON: { actor_user_id, item_id }
public static function delete(mysqli $conn): void {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') error_response('Method not allowed', 405);
  $d=read_json(); $missing=require_fields($d,['actor_user_id','item_id']); if($missing) error_response("Missing field: $missing",400);

  $actor=(int)$d['actor_user_id']; if(!is_admin_user($conn,$actor)) error_response('Unauthorized (admin only)',401);
  $id=(int)$d['item_id']; if($id<=0) error_response('Invalid item_id',400);

  // Try delete and catch FK error (MySQL 1451)
  $st=$conn->prepare("DELETE FROM menu_items WHERE item_id=?");
  $st->bind_param('i',$id);
  if (!$st->execute()){
    $errno = $conn->errno;
    $st->close();
    // 1451 => Cannot delete or update a parent row (FK restrict)
    if ($errno === 1451){
      // Use 409 Conflict for state conflict
      error_response('Cannot delete: item is referenced by existing orders. Mark as unavailable instead.', 409);
    }
    error_response('DB error deleting item', 500);
  }
  $st->close();
  respond(['message'=>'Deleted','item_id'=>$id]);
}
