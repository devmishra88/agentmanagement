import { useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import { toggleMenu } from "../slices/CommonSlice";

const useSwitchRoute = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const switchRoute = (link, replacehistory = true) => {
    dispatch(toggleMenu({ menuposition: `left`, menustatus: false }))
    navigate(link, { replace: replacehistory });
  };

  return switchRoute;
};

export default useSwitchRoute;
