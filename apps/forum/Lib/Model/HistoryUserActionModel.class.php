<?php
class HistoryUserActionModel extends Model{
	protected $tableName = "history_user_action";
	
	//迁移历史数据
	public function changeData(){
		
		$res1 = $this->execute("INSERT INTO ".C('DB_PREFIX')."history_user_action FROM ts_user_action");
		
		if(!$res){
			die( "Insert error! \n" );
		}

		$res2 = $this->execute("DELETE FROM ".C('DB_PREFIX')."user_action");
		
		if(!$res2){
			die( "Delete error! \n" );
		}

		echo "Move success!";
	}
}
?>