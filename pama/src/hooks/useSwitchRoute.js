import { useNavigate } from "react-router-dom";

const useSwitchRoute = () => {
  const navigate = useNavigate();

  const switchRoute = (link, history = true) => {
    navigate(link, { replace: history });
  };

  return switchRoute;
};

export default useSwitchRoute;
