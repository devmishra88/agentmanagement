import React, { useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import { toggleLoader, confirmDelete } from "../slices/CommonSlice";
import {
  AppHeader,
  SingleGeneralDatacard,
  AddFab,
  Title,
  ManageContainer,
} from "../components";
import useSwitchRoute from "../hooks/useSwitchRoute";
import { useNewspapersData } from "../hooks/useNewspaperData";

function ManageNewspaper() {
  const dispatch = useDispatch();
  const { menuposition, menustatus, candelete, deletionrecordid } = useSelector(
    (state) => state.common
  );
  const switchRoute = useSwitchRoute();

  const onSuccess = (data) => {
    // console.log(`Perform side effect after data fetching`, data);
  };

  const onError = (error) => {
    // console.log(`Perform side effect after encountering error`, error);
  };

  const {
    isLoading,
    isFetching,
    data,
    isError,
    error,
    deleteArea /*, refetch*/,
  } = useNewspapersData(onSuccess, onError);

  useEffect(() => {
    dispatch(toggleLoader({ loaderstatus: isLoading || isFetching }));
  }, [isLoading, isFetching]);

  useEffect(() => {
    if (candelete) {
      dispatch(toggleLoader({ loaderstatus: isLoading || isFetching }));
      deleteArea(deletionrecordid);
    }
  }, [deletionrecordid, candelete]);

  if (isError) {
    return <h2>{error?.message}</h2>;
  }

  return (
    <>
      <AppHeader>Manage Newspaper</AppHeader>
      <ManageContainer>
        {data?.data?.recordlist?.length > 0 ? (
          <Title>Total Newspaper : {data?.data?.recordlist?.length}</Title>
        ) : null}
        {data?.data?.recordlist?.map((area, index) => {
          return (
            <SingleGeneralDatacard
              key={area.id}
              {...area}
              formroute="newspaper"
              deleteCallback={() =>
                dispatch(
                  confirmDelete({
                    deletepopupstatus: true,
                    deletepopupposition: "bottom",
                    candelete: false,
                    deletionrecordid: area.id,
                  })
                )
              }
            />
          );
        })}
      </ManageContainer>
      <AddFab onClick={() => switchRoute(`/newspaper`, false)}>
        Add Newspaper
      </AddFab>
    </>
  );
}

export default ManageNewspaper;
